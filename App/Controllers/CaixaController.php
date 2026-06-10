<?php

namespace App\Controllers;

use App\Config\ConfigPerfil;
use App\Models\Caixa;
use App\Models\CaixaSessao;
use App\Rules\Logged;
use System\Controller\Controller;
use System\Get\Get;
use System\Post\Post;
use System\Session\Session;

class CaixaController extends Controller
{
    protected string $layout = 'default';
    protected Post $post;
    protected Get $get;
    protected int $idEmpresa;
    protected int $idUsuario;

    public function __construct()
    {
        parent::__construct();
        $this->post = new Post();
        $this->get = new Get();
        $this->idEmpresa = (int) Session::get('idEmpresa');
        $this->idUsuario = (int) Session::get('idUsuario');

        (new Logged())->isValid();
    }

    public function save(): void
    {
        if ((int) Session::get('idPerfil') !== ConfigPerfil::superAdmin()) {
            Session::flash('error', 'Somente o SuperAdmin pode cadastrar caixas.');
            $this->get->redirectTo('home');
        }

        if (!$this->post->hasPost()) {
            $this->get->redirectTo('home');
        }

        $nome = trim($this->post->data()->nome ?? '');
        $descricao = trim($this->post->data()->descricao ?? '');

        if ($nome === '') {
            Session::flash('error', 'Informe o nome do caixa.');
            $this->get->redirectTo('home');
        }

        (new Caixa())->save([
            'id_empresa' => $this->idEmpresa,
            'nome' => $nome,
            'descricao' => $descricao ?: null,
            'status' => 1,
        ]);

        Session::flash('success', 'Caixa cadastrado com sucesso.');
        $this->get->redirectTo('home');
    }

    public function abrir(): void
    {
        if ((int) Session::get('idPerfil') !== ConfigPerfil::vendedor()) {
            Session::flash('error', 'A abertura de caixa está disponível apenas para vendedores.');
            $this->get->redirectTo('home');
        }

        $idCaixa = (int) ($this->post->data()->id_caixa ?? 0);
        $valorAbertura = formataValorMoedaParaGravacao($this->post->data()->valor_abertura ?? '0');
        $observacao = trim($this->post->data()->observacao_abertura ?? '');

        $resultado = (new CaixaSessao())->abrir(
            $this->idEmpresa,
            $this->idUsuario,
            $idCaixa,
            (float) $valorAbertura,
            $observacao ?: null
        );

        if (!$resultado->status) {
            Session::flash('error', $resultado->message);
            $this->get->redirectTo('home');
        }

        $caixa = (new Caixa())->find($idCaixa);
        Session::set('idCaixaSessao', $resultado->sessao->id);
        Session::set('idCaixaAtual', $idCaixa);
        Session::set('nomeCaixaAtual', $caixa->nome ?? null);

        Session::flash('success', 'Caixa aberto com sucesso.');
        $this->get->redirectTo('home');
    }

    public function fechar(): void
    {
        if ((int) Session::get('idPerfil') !== ConfigPerfil::vendedor()) {
            Session::flash('error', 'O fechamento de caixa está disponível apenas para vendedores.');
            $this->get->redirectTo('home');
        }

        $valorFechamento = formataValorMoedaParaGravacao($this->post->data()->valor_fechamento ?? '0');
        $observacao = trim($this->post->data()->observacao_fechamento ?? '');

        $resultado = (new CaixaSessao())->fecharPorUsuario(
            $this->idEmpresa,
            $this->idUsuario,
            (float) $valorFechamento,
            $observacao ?: null
        );

        if (!$resultado->status) {
            Session::flash('error', $resultado->message);
            $this->get->redirectTo('home');
        }

        Session::delete('idCaixaSessao');
        Session::delete('idCaixaAtual');
        Session::delete('nomeCaixaAtual');
        Session::flash('success', 'Caixa fechado com sucesso.');
        $this->get->redirectTo('home');
    }

    public function ping(): void
    {
        (new CaixaSessao())->atualizarAtividade($this->idEmpresa, $this->idUsuario);
        echo json_encode(['status' => true]);
    }
}
