<?php

namespace App\Models;

use App\Config\ConfigPerfil;
use System\Auth\Auth;
use System\Model\Model;

class Usuario extends Model
{
    use Auth;

    protected string $table      = 'usuarios';
    protected bool   $timestamps = true;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lista usuários da empresa com filtro por perfil
     */
    public function usuarios(int $idEmpresa, int $idUsuarioLogado = 0, int $idPerfilUsuarioLogado = 0): array
    {
        if ($idEmpresa <= 0) {
            return [];
        }

        $superAdmin  = ConfigPerfil::superAdmin();
        $vendedor    = ConfigPerfil::vendedor();

        $whereExtra = '';
        if ($idPerfilUsuarioLogado && $idPerfilUsuarioLogado !== $superAdmin) {
            $whereExtra = " AND u.id_perfil NOT IN({$superAdmin})";
        }
        if ($idPerfilUsuarioLogado && $idPerfilUsuarioLogado === $vendedor) {
            $whereExtra = " AND u.id = {$idUsuarioLogado}";
        }

        return $this->query("
            SELECT u.id, u.nome, u.email, u.id_sexo, u.id_perfil,
                   u.created_at, u.imagem, u.deleted_at, u.status,
                   s.descricao AS sexo, p.descricao AS perfil
            FROM usuarios u
            INNER JOIN sexos s ON u.id_sexo = s.id
            INNER JOIN perfis p ON u.id_perfil = p.id
            WHERE u.id_empresa = {$idEmpresa} {$whereExtra}
            ORDER BY u.nome
        ");
    }

    public function verificaSeEmailExiste(string $email): bool
    {
        if (!$email) return false;
        $result = $this->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1", [$email]);
        return count($result) > 0;
    }

    public function seDadoNaoPertenceAoUsuarioEditado(string $campo, string $valor, int $idUsuario): bool
    {
        $row = $this->findBy($campo, $valor);
        return $row && (int)$row->id !== $idUsuario;
    }
}
