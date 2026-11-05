<?php

namespace App\Rules;

use App\Config\ConfigPerfil;
use App\Models\CaixaSessao;
use System\Get\Get;
use System\Session\Session;

class CaixaAbertoRule
{
    public function validate(): void
    {
        if ((int) Session::get('idPerfil') !== ConfigPerfil::vendedor()) {
            return;
        }

        $sessao = (new CaixaSessao())->sessaoAbertaDoUsuario(
            (int) Session::get('idEmpresa'),
            (int) Session::get('idUsuario')
        );

        if (!$sessao) {
            if ($this->isAjaxRequest()) {
                http_response_code(403);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'status' => false,
                    'caixa_required' => true,
                    'message' => 'Abra um caixa nesta aba antes de continuar.',
                ]);
                exit;
            }
            Session::flash('info', 'Abra um caixa antes de continuar.');
            (new Get())->redirectTo('home');
        }

        Session::set('idCaixaSessao', $sessao->id);
        Session::set('idCaixaAtual', $sessao->id_caixa);
        Session::set('nomeCaixaAtual', $sessao->caixa_nome ?? null);
    }

    private function isAjaxRequest(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || isset($_SERVER['HTTP_X_TAB_ID']);
    }
}
