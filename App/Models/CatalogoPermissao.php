<?php

namespace App\Models;

use System\Model\Model;

class CatalogoPermissao extends Model
{
    public const GERENCIAR_CATALOGO = 'gerenciar_catalogo_produtos';

    protected string $table = 'catalogo_permissoes';

    public function usuarioPossui(int $idEmpresa, int $idUsuario, string $permissao = self::GERENCIAR_CATALOGO): bool
    {
        if ($idEmpresa <= 0 || $idUsuario <= 0) {
            return false;
        }

        $row = $this->prepareGetOne(
            "SELECT p.id FROM catalogo_permissoes p
             INNER JOIN usuarios u ON u.id = p.id_usuario
             WHERE p.id_empresa = :empresa AND p.id_usuario = :usuario AND p.permissao = :permissao
               AND u.id_empresa = :empresa2 AND u.deleted_at IS NULL AND u.status = 1
             LIMIT 1",
            [':empresa' => $idEmpresa, ':usuario' => $idUsuario, ':permissao' => $permissao, ':empresa2' => $idEmpresa]
        );

        return $row !== null;
    }

    public function titularDaEmpresa(int $idEmpresa, string $permissao = self::GERENCIAR_CATALOGO): ?object
    {
        return $this->prepareGetOne(
            "SELECT p.id_usuario, p.concedido_por, p.created_at, u.nome, u.email
             FROM catalogo_permissoes p
             INNER JOIN usuarios u ON u.id = p.id_usuario
             WHERE p.id_empresa = :empresa AND p.permissao = :permissao
             LIMIT 1",
            [':empresa' => $idEmpresa, ':permissao' => $permissao]
        );
    }

    public function usuariosElegiveis(int $idEmpresa): array
    {
        return $this->prepare(
            "SELECT id, nome, email FROM usuarios
             WHERE id_empresa = :empresa AND deleted_at IS NULL AND status = 1
             ORDER BY nome",
            [':empresa' => $idEmpresa]
        );
    }

    public function conceder(int $idEmpresa, int $idUsuario, int $concedidoPor, string $permissao = self::GERENCIAR_CATALOGO): void
    {
        $this->db->beginTransaction();
        try {
            $del = $this->db->prepare("DELETE FROM catalogo_permissoes WHERE id_empresa = ? AND permissao = ?");
            $del->execute([$idEmpresa, $permissao]);

            $ins = $this->db->prepare(
                "INSERT INTO catalogo_permissoes (id_empresa, id_usuario, permissao, concedido_por) VALUES (?, ?, ?, ?)"
            );
            $ins->execute([$idEmpresa, $idUsuario, $permissao, $concedidoPor]);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function revogar(int $idEmpresa, string $permissao = self::GERENCIAR_CATALOGO): void
    {
        $stmt = $this->db->prepare("DELETE FROM catalogo_permissoes WHERE id_empresa = ? AND permissao = ?");
        $stmt->execute([$idEmpresa, $permissao]);
    }
}
