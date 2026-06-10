<?php

namespace App\Rules;

use App\Config\ConfigPerfil;
use System\Get\Get;
use System\Session\Session;

class PerfilAcessoRule
{
    private Get $get;
    private int $idPerfilUsuarioLogado;
    private int $idUsuarioLogado;

    public function __construct()
    {
        $this->get = new Get();
        $this->idPerfilUsuarioLogado = (int) Session::get('idPerfil');
        $this->idUsuarioLogado = (int) Session::get('idUsuario');
    }

    public function exigePerfis(array $perfisPermitidos, string $redirectTo = 'home', ?string $message = null): void
    {
        if (in_array($this->idPerfilUsuarioLogado, $perfisPermitidos, true)) {
            return;
        }

        $this->negarAcesso($redirectTo, $message);
    }

    public function negaPerfis(array $perfisNegados, string $redirectTo = 'home', ?string $message = null): void
    {
        if (!in_array($this->idPerfilUsuarioLogado, $perfisNegados, true)) {
            return;
        }

        $this->negarAcesso($redirectTo, $message);
    }

    public function podeGerenciarUsuario(int $idUsuarioAlvo, int $idPerfilAlvo): bool
    {
        if ($this->idPerfilUsuarioLogado === ConfigPerfil::superAdmin()) {
            return true;
        }

        if ($idUsuarioAlvo === $this->idUsuarioLogado) {
            return true;
        }

        if ($this->idPerfilUsuarioLogado === ConfigPerfil::gerente()) {
            return $idPerfilAlvo === ConfigPerfil::vendedor();
        }

        return false;
    }

    private function negarAcesso(string $redirectTo, ?string $message = null): void
    {
        Session::flash('error', $message ?: 'Você não tem permissão para acessar este recurso.');
        $this->get->redirectTo($redirectTo);
    }
}
