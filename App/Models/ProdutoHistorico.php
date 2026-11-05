<?php

namespace App\Models;

use System\Model\Model;

/**
 * Histórico de alterações comerciais. Somente inserção e consulta:
 * não existe método de atualização ou exclusão.
 */
class ProdutoHistorico extends Model
{
    protected string $table = 'produtos_historico';

    public function registrar(int $idEmpresa, int $idProduto, int $idUsuario, string $operacao, ?string $campo, $anterior, $novo): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO produtos_historico (id_empresa, id_produto, id_usuario, operacao, campo, valor_anterior, valor_novo)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $idEmpresa,
            $idProduto,
            $idUsuario,
            $operacao,
            $campo,
            $anterior === null ? null : (string) $anterior,
            $novo === null ? null : (string) $novo,
        ]);
    }

    public function listar(int $idEmpresa, ?int $idProduto = null, int $limite = 200): array
    {
        $limite = max(1, min(500, $limite));
        $sql    = "SELECT h.id, h.id_produto, h.operacao, h.campo, h.valor_anterior, h.valor_novo, h.created_at,
                          u.nome AS usuario, p.nome AS produto
                   FROM produtos_historico h
                   LEFT JOIN usuarios u ON u.id = h.id_usuario
                   LEFT JOIN produtos p ON p.id = h.id_produto AND p.id_empresa = h.id_empresa
                   WHERE h.id_empresa = :empresa";
        $params = [':empresa' => $idEmpresa];

        if ($idProduto) {
            $sql .= " AND h.id_produto = :produto";
            $params[':produto'] = $idProduto;
        }

        $sql .= " ORDER BY h.id DESC LIMIT {$limite}";
        return $this->prepare($sql, $params);
    }
}
