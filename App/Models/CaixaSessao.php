<?php

namespace App\Models;

use System\Model\Model;

class CaixaSessao extends Model
{
    protected string $table = 'caixa_sessoes';
    protected bool $timestamps = true;

    public function __construct()
    {
        parent::__construct();
        (new Caixa())->ensureSchema();
    }

    public function sessaoAbertaDoUsuario(int $idEmpresa, int $idUsuario): ?object
    {
        return $this->prepareGetOne(
            "SELECT
                cs.*,
                c.nome AS caixa_nome,
                c.descricao AS caixa_descricao,
                COALESCE((
                    SELECT SUM(v.valor)
                    FROM vendas v
                    WHERE v.id_empresa = cs.id_empresa
                      AND v.id_usuario = cs.id_usuario
                      AND v.deleted_at IS NULL
                      AND v.created_at BETWEEN cs.aberto_em AND COALESCE(cs.fechado_em, NOW())
                ), 0) AS faturamento,
                COALESCE((
                    SELECT COUNT(DISTINCT CASE
                        WHEN v.codigo_venda IS NOT NULL AND v.codigo_venda <> '' THEN v.codigo_venda
                        ELSE CONCAT('VENDA-', v.id)
                    END)
                    FROM vendas v
                    WHERE v.id_empresa = cs.id_empresa
                      AND v.id_usuario = cs.id_usuario
                      AND v.deleted_at IS NULL
                      AND v.created_at BETWEEN cs.aberto_em AND COALESCE(cs.fechado_em, NOW())
                ), 0) AS quantidade_vendas
            FROM caixa_sessoes cs
            INNER JOIN caixas c ON c.id = cs.id_caixa
            WHERE cs.id_empresa = ?
              AND cs.id_usuario = ?
              AND cs.fechado_em IS NULL
              AND cs.deleted_at IS NULL
            ORDER BY cs.id DESC
            LIMIT 1",
            [$idEmpresa, $idUsuario]
        );
    }

    public function sessaoAbertaDoCaixa(int $idEmpresa, int $idCaixa): ?object
    {
        return $this->prepareGetOne(
            "SELECT *
            FROM caixa_sessoes
            WHERE id_empresa = ?
              AND id_caixa = ?
              AND fechado_em IS NULL
              AND deleted_at IS NULL
            ORDER BY id DESC
            LIMIT 1",
            [$idEmpresa, $idCaixa]
        );
    }

    public function abrir(int $idEmpresa, int $idUsuario, int $idCaixa, float $valorAbertura = 0, ?string $observacao = null): object
    {
        $sessaoUsuario = $this->sessaoAbertaDoUsuario($idEmpresa, $idUsuario);
        if ($sessaoUsuario) {
            return (object) ['status' => false, 'message' => 'Este vendedor já está com um caixa aberto.'];
        }

        $sessaoCaixa = $this->sessaoAbertaDoCaixa($idEmpresa, $idCaixa);
        if ($sessaoCaixa) {
            return (object) ['status' => false, 'message' => 'Este caixa já está em uso por outro usuário.'];
        }

        $caixa = (new Caixa())->find($idCaixa);
        if (!$caixa || (int) $caixa->id_empresa !== $idEmpresa || (int) $caixa->status !== 1) {
            return (object) ['status' => false, 'message' => 'Caixa inválido ou inativo.'];
        }

        $idSessao = $this->save([
            'id_empresa' => $idEmpresa,
            'id_caixa' => $idCaixa,
            'id_usuario' => $idUsuario,
            'valor_abertura' => $valorAbertura,
            'observacao_abertura' => $observacao,
            'aberto_em' => timestamp(),
            'ultima_atividade_em' => timestamp(),
        ]);

        return (object) ['status' => true, 'sessao' => $this->find((int) $idSessao)];
    }

    public function fecharPorUsuario(int $idEmpresa, int $idUsuario, float $valorFechamento = 0, ?string $observacao = null): object
    {
        $sessao = $this->sessaoAbertaDoUsuario($idEmpresa, $idUsuario);
        if (!$sessao) {
            return (object) ['status' => false, 'message' => 'Nenhum caixa aberto para este usuário.'];
        }

        $this->update([
            'valor_fechamento' => $valorFechamento,
            'observacao_fechamento' => $observacao,
            'fechado_em' => timestamp(),
            'ultima_atividade_em' => timestamp(),
        ], (int) $sessao->id);

        return (object) ['status' => true];
    }

    public function atualizarAtividade(int $idEmpresa, int $idUsuario): void
    {
        $sessao = $this->sessaoAbertaDoUsuario($idEmpresa, $idUsuario);
        if (!$sessao) {
            return;
        }

        $this->update(['ultima_atividade_em' => timestamp()], (int) $sessao->id);
    }
}
