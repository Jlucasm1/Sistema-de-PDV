<?php

namespace App\Controllers;

use App\Models\MeioPagamento;
use App\Models\Usuario;
use App\Models\Venda;
use App\Repositories\VendasDoDiaRepository;
use App\Rules\AcessoAoTipoDePdv;
use App\Rules\Logged;
use System\Controller\Controller;
use System\Get\Get;
use System\Post\Post;
use System\Session\Session;

class PdvPadraoController extends Controller
{
    protected string $layout;
    protected int    $idEmpresa;
    protected int    $idUsuario;
    protected int    $idPerfilUsuarioLogado;
    protected Post   $post;
    protected Get    $get;

    public function __construct()
    {
        parent::__construct();
        $this->layout                = 'default';
        $this->post                  = new Post();
        $this->get                   = new Get();
        $this->idEmpresa             = (int) Session::get('idEmpresa');
        $this->idUsuario             = (int) Session::get('idUsuario');
        $this->idPerfilUsuarioLogado = (int) Session::get('idPerfil');

        (new Logged())->isValid();
        (new AcessoAoTipoDePdv())->validate();
    }

    public function index(): void
    {
        $vendasDoDiaRepo = new VendasDoDiaRepository();

        $vendasGeralDoDia                      = $vendasDoDiaRepo->vendasGeralDoDia($this->idEmpresa, 10);
        $totalVendasNoDia                      = $vendasDoDiaRepo->totalVendasNoDia($this->idEmpresa);
        $totalValorVendaPorMeioDePagamentoNoDia = $vendasDoDiaRepo->totalValorVendaPorMeioDePagamentoNoDia($this->idEmpresa);
        $totalVendaNoDiaAnterior               = $vendasDoDiaRepo->totalVendasNoDia($this->idEmpresa, decrementDaysFromDate(1));

        $meiosPagamentos = (new MeioPagamento())->all();
        $usuarios        = (new Usuario())->usuarios($this->idEmpresa, $this->idUsuario, $this->idPerfilUsuarioLogado);

        $this->view('pdv/padrao', $this->layout, compact(
            'vendasGeralDoDia', 'meiosPagamentos', 'usuarios',
            'totalVendasNoDia', 'totalValorVendaPorMeioDePagamentoNoDia', 'totalVendaNoDiaAnterior'
        ));
    }

    public function save(): void
    {
        if (!$this->post->hasPost()) return;

        $dados                = (array) $this->post->data();
        $dados['id_empresa']  = $this->idEmpresa;
        $dados['valor']       = formataValorMoedaParaGravacao($dados['valor']);

        try {
            (new Venda())->save($dados);
            $this->get->redirectTo('pdvPadrao');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function desativarVenda(int $idVenda): void
    {
        try {
            (new Venda())->update(['deleted_at' => timestamp()], $idVenda);
            echo json_encode(['status' => true]);
        } catch (\Exception $e) {
            echo json_encode(['status' => false, 'error' => $e->getMessage()]);
        }
    }
}
