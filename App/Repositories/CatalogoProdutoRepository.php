<?php

namespace App\Repositories;

use App\Models\ProdutoHistorico;
use App\Services\Catalogo\PrecoCalculator;
use System\Model\Model;

/**
 * Catálogo comercial de produtos. Nunca lê nem altera estoque, vendas,
 * pedidos ou caixa; apenas catálogo (produtos) e histórico.
 */
class CatalogoProdutoRepository extends Model
{
    protected string $table = 'produtos';

    private const COLUNAS = 'id, nome, descricao, codigo, codigo_interno, preco, preco_minimo, preco_compra';

    private const CAMPOS_AUDITADOS = [
        'nome'           => 'nome',
        'descricao'      => 'descricao',
        'codigo_interno' => 'codigo_interno',
        'codigo'         => 'codigo',
        'preco'          => 'preco',
        'preco_minimo'   => 'preco_minimo',
        'preco_compra'   => 'preco_compra',
    ];

    private const CAMPOS_MONETARIOS = ['preco', 'preco_minimo', 'preco_compra'];

    private ProdutoHistorico $historico;

    public function __construct()
    {
        parent::__construct();
        $this->historico = new ProdutoHistorico();
    }

    public function buscar(int $idEmpresa, string $termo = '', int $limite = 30): array
    {
        if ($idEmpresa <= 0) {
            return [];
        }

        $limite = max(1, min(200, $limite));
        $sql    = "SELECT " . self::COLUNAS . " FROM produtos WHERE id_empresa = :empresa AND deleted_at IS NULL";
        $params = [':empresa' => $idEmpresa];

        $palavras = preg_split('/\s+/u', trim($termo), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach (array_slice($palavras, 0, 5) as $i => $palavra) {
            $like = '%' . addcslashes(mb_substr($palavra, 0, 100), '\\%_') . '%';
            $sql .= " AND (nome LIKE :n{$i} OR descricao LIKE :d{$i} OR codigo LIKE :c{$i} OR codigo_interno LIKE :i{$i})";
            $params[":n{$i}"] = $like;
            $params[":d{$i}"] = $like;
            $params[":c{$i}"] = $like;
            $params[":i{$i}"] = $like;
        }

        $exato = trim($termo);
        $sql  .= " ORDER BY (codigo = :ex1 OR codigo_interno = :ex2) DESC, nome ASC LIMIT {$limite}";
        $params[':ex1'] = $exato;
        $params[':ex2'] = $exato;

        return $this->prepare($sql, $params);
    }

    public function obter(int $idEmpresa, int $idProduto): ?object
    {
        return $this->prepareGetOne(
            "SELECT " . self::COLUNAS . " FROM produtos WHERE id = :id AND id_empresa = :empresa AND deleted_at IS NULL",
            [':id' => $idProduto, ':empresa' => $idEmpresa]
        );
    }

    /**
     * @return array{ok:bool, erro?:string, id?:int}
     */
    public function criar(int $idEmpresa, int $idUsuario, array $dados, bool $podeAlterarPrecoOficial): array
    {
        $v = $this->validar($idEmpresa, null, $dados, null, $podeAlterarPrecoOficial);
        if (isset($v['erro'])) {
            return ['ok' => false, 'erro' => $v['erro']];
        }
        $d = $v['dados'];

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO produtos (id_empresa, nome, descricao, codigo, codigo_interno, preco, preco_minimo, preco_compra, mostrar_em_vendas, ativar_quantidade, quantidade)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0)"
            );
            $stmt->execute([
                $idEmpresa,
                $d['nome'],
                $d['descricao'],
                $d['codigo'],
                $d['codigo_interno'],
                $d['preco'],
                $d['preco_minimo'],
                $d['preco_compra'],
                (float) $d['preco'] > 0 ? 1 : 0,
            ]);
            $idProduto = (int) $this->db->lastInsertId();

            $this->historico->registrar($idEmpresa, $idProduto, $idUsuario, 'cadastro', null, null, $d['nome']);
            foreach (self::CAMPOS_AUDITADOS as $campo) {
                $valor = $d[$campo] ?? null;
                if ($valor !== null && $valor !== '' && $valor !== '0.00') {
                    $this->historico->registrar($idEmpresa, $idProduto, $idUsuario, 'cadastro', $campo, null, $valor);
                }
            }

            $this->db->commit();
            return ['ok' => true, 'id' => $idProduto];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return ['ok' => false, 'erro' => 'Não foi possível cadastrar o produto.'];
        }
    }

    /**
     * @return array{ok:bool, erro?:string}
     */
    public function atualizar(int $idEmpresa, int $idUsuario, int $idProduto, array $dados, bool $podeAlterarPrecoOficial): array
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "SELECT " . self::COLUNAS . " FROM produtos WHERE id = ? AND id_empresa = ? AND deleted_at IS NULL FOR UPDATE"
            );
            $stmt->execute([$idProduto, $idEmpresa]);
            $atual = $stmt->fetch(\PDO::FETCH_OBJ);
            if (!$atual) {
                $this->db->rollBack();
                return ['ok' => false, 'erro' => 'Produto não encontrado.'];
            }

            $v = $this->validar($idEmpresa, $idProduto, $dados, $atual, $podeAlterarPrecoOficial);
            if (isset($v['erro'])) {
                $this->db->rollBack();
                return ['ok' => false, 'erro' => $v['erro']];
            }
            $d = $v['dados'];

            $alteracoes = [];
            foreach (self::CAMPOS_AUDITADOS as $campo) {
                $antes  = $atual->{$campo} === null ? null : (string) $atual->{$campo};
                $depois = $d[$campo] === null ? null : (string) $d[$campo];
                if (in_array($campo, self::CAMPOS_MONETARIOS, true)) {
                    $antes = $antes === null ? null : PrecoCalculator::formatarCentavos((int) PrecoCalculator::paraCentavos($antes));
                }
                if ($antes !== $depois) {
                    $alteracoes[$campo] = [$antes, $depois];
                }
            }

            if ($alteracoes) {
                $sets   = [];
                $values = [];
                foreach ($alteracoes as $campo => [, $novo]) {
                    $sets[]   = "{$campo} = ?";
                    $values[] = $novo;
                }
                $values[] = $idProduto;
                $values[] = $idEmpresa;
                $upd = $this->db->prepare("UPDATE produtos SET " . implode(', ', $sets) . " WHERE id = ? AND id_empresa = ?");
                $upd->execute($values);

                foreach ($alteracoes as $campo => [$antes, $novo]) {
                    $this->historico->registrar($idEmpresa, $idProduto, $idUsuario, 'edicao', $campo, $antes, $novo);
                }
            }

            $this->db->commit();
            return ['ok' => true];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['ok' => false, 'erro' => 'Não foi possível atualizar o produto.'];
        }
    }

    /**
     * Exclusão lógica. Não toca em vendas, pedidos ou histórico.
     */
    public function excluir(int $idEmpresa, int $idUsuario, int $idProduto): bool
    {
        $this->db->beginTransaction();
        try {
            $produto = $this->obter($idEmpresa, $idProduto);
            if (!$produto) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare(
                "UPDATE produtos SET deleted_at = ? WHERE id = ? AND id_empresa = ? AND deleted_at IS NULL"
            );
            $stmt->execute([date('Y-m-d H:i:s'), $idProduto, $idEmpresa]);

            $this->historico->registrar($idEmpresa, $idProduto, $idUsuario, 'exclusao', null, $produto->nome, null);
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Valida e normaliza. Preço oficial só é aceito de quem pode alterá-lo;
     * para os demais, o valor existente é mantido (ou 0.00 em cadastro novo).
     *
     * @return array{dados?:array<string,mixed>, erro?:string}
     */
    private function validar(int $idEmpresa, ?int $idProduto, array $in, ?object $atual, bool $podePrecoOficial): array
    {
        $nome = trim((string) ($in['nome'] ?? ''));
        if ($nome === '') {
            return ['erro' => 'Informe o nome do produto.'];
        }
        if (mb_strlen($nome) > 100) {
            return ['erro' => 'O nome pode ter no máximo 100 caracteres.'];
        }

        $descricao = trim((string) ($in['descricao'] ?? ''));
        if (mb_strlen($descricao) > 2000) {
            return ['erro' => 'A descrição pode ter no máximo 2000 caracteres.'];
        }

        $codigoInterno = trim((string) ($in['codigo_interno'] ?? ''));
        $codigoBarras  = trim((string) ($in['codigo'] ?? ''));
        if (mb_strlen($codigoInterno) > 50 || mb_strlen($codigoBarras) > 100) {
            return ['erro' => 'Código interno (máx. 50) ou código de barras (máx. 100) muito longo.'];
        }

        $precoMinimoCent = PrecoCalculator::paraCentavos($in['preco_minimo'] ?? '0');
        if ($precoMinimoCent === null) {
            return ['erro' => 'Preço mínimo inválido.'];
        }

        if (array_key_exists('preco_compra', $in)) {
            $custoInformado = trim((string) $in['preco_compra']);
            $custoCent = PrecoCalculator::paraCentavos($custoInformado === '' ? '0' : $custoInformado);
            if ($custoCent === null) {
                return ['erro' => 'Valor de custo inválido.'];
            }
        } else {
            $custoCent = $atual ? (int) PrecoCalculator::paraCentavos((string) ($atual->preco_compra ?? '0')) : 0;
        }

        if ($podePrecoOficial && isset($in['preco']) && trim((string) $in['preco']) !== '') {
            $precoCent = PrecoCalculator::paraCentavos($in['preco']);
            if ($precoCent === null || $precoCent <= 0) {
                return ['erro' => 'Preço oficial inválido.'];
            }
        } else {
            $precoCent = $atual ? (int) PrecoCalculator::paraCentavos((string) $atual->preco) : 0;
        }

        if ($precoCent > 0 && $precoMinimoCent > $precoCent) {
            return ['erro' => 'O preço mínimo não pode ser maior que o preço oficial (R$ ' . PrecoCalculator::formatarCentavos($precoCent) . ').'];
        }

        if ($codigoInterno !== '' && $this->codigoEmUso($idEmpresa, 'codigo_interno', $codigoInterno, $idProduto)) {
            return ['erro' => 'Já existe um produto com este código interno.'];
        }
        if ($codigoBarras !== '' && $this->codigoEmUso($idEmpresa, 'codigo', $codigoBarras, $idProduto)) {
            return ['erro' => 'Já existe um produto com este código de barras.'];
        }

        return ['dados' => [
            'nome'           => $nome,
            'descricao'      => $descricao === '' ? null : $descricao,
            'codigo_interno' => $codigoInterno === '' ? null : $codigoInterno,
            'codigo'         => $codigoBarras === '' ? null : $codigoBarras,
            'preco'          => PrecoCalculator::formatarCentavos($precoCent),
            'preco_minimo'   => PrecoCalculator::formatarCentavos($precoMinimoCent),
            'preco_compra'   => PrecoCalculator::formatarCentavos($custoCent),
        ]];
    }

    private function codigoEmUso(int $idEmpresa, string $coluna, string $valor, ?int $ignorarId): bool
    {
        if (!in_array($coluna, ['codigo', 'codigo_interno'], true)) {
            return false;
        }
        $row = $this->prepareGetOne(
            "SELECT id FROM produtos WHERE id_empresa = :empresa AND {$coluna} = :valor AND deleted_at IS NULL AND id <> :ignorar LIMIT 1",
            [':empresa' => $idEmpresa, ':valor' => $valor, ':ignorar' => $ignorarId ?? 0]
        );
        return $row !== null;
    }
}
