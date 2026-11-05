<?php

namespace App\Rules;

use App\Config\ConfigPerfil;
use App\Models\CatalogoPermissao;
use System\Session\Session;

/**
 * Regras de acesso do catálogo. Sempre consultadas no backend.
 */
class CatalogoAcesso
{
    private int $idUsuario;
    private int $idPerfil;
    private int $idEmpresa;

    public function __construct(?int $idUsuario = null, ?int $idPerfil = null, ?int $idEmpresa = null)
    {
        $this->idUsuario = $idUsuario ?? (int) Session::get('idUsuario');
        $this->idPerfil  = $idPerfil ?? (int) Session::get('idPerfil');
        $this->idEmpresa = $idEmpresa ?? (int) Session::get('idEmpresa');
    }

    public function idEmpresa(): int
    {
        return $this->idEmpresa;
    }

    public function idUsuario(): int
    {
        return $this->idUsuario;
    }

    public function ehAdministrativo(): bool
    {
        return in_array($this->idPerfil, [
            ConfigPerfil::superAdmin(),
            ConfigPerfil::adiministrador(),
            ConfigPerfil::gerente(),
        ], true);
    }

    public function podeGerenciarCatalogo(): bool
    {
        if ($this->idEmpresa <= 0 || $this->idUsuario <= 0) {
            return false;
        }
        return $this->ehAdministrativo()
            || (new CatalogoPermissao())->usuarioPossui($this->idEmpresa, $this->idUsuario);
    }

    public function podeAlterarPrecoOficial(): bool
    {
        return $this->idEmpresa > 0 && $this->idUsuario > 0 && $this->ehAdministrativo();
    }

    public function podeConcederPermissao(): bool
    {
        return $this->idEmpresa > 0 && in_array($this->idPerfil, [
            ConfigPerfil::superAdmin(),
            ConfigPerfil::adiministrador(),
        ], true);
    }
}
