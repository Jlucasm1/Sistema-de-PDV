<?php

namespace App\Controllers;

use App\Config\ConfigPerfil;
use App\Models\Empresa;
use App\Repositories\RelatorioVendasPorPeriodoRepository;
use App\Rules\Logged;
use App\Rules\PerfilAcessoRule;
use App\Services\Usuarios\BuscaUsuariosService;
use System\Controller\Controller;
use System\Get\Get;
use System\Post\Post;
use System\Session\Session;

class RelatorioController extends Controller
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
        (new PerfilAcessoRule())->negaPerfis(
            [ConfigPerfil::vendedor()],
            'home',
            'Relatórios estão disponíveis apenas para gerentes e administradores.'
        );
    }

    public function index(): void
    {
        $this->get->redirectTo('relatorio/vendasPorPeriodo');
    }

    public function vendasPorPeriodo(): void
    {
        $relatorioVendas               = new RelatorioVendasPorPeriodoRepository();
        $periodoDisponivelParaConsulta = $relatorioVendas->periodoDisponivelParaConsulta($this->idEmpresa);
        $usuarios                      = (new BuscaUsuariosService())->ativos($this->idEmpresa, $this->idPerfilUsuarioLogado);

        $this->view('relatorio/vendasPorPeriodo/index', $this->layout, compact(
            'periodoDisponivelParaConsulta',
            'usuarios'
        ));
    }

    public function vendasChamadaAjax(): void
    {
        if (!$this->post->hasPost()) return;

        $de        = $this->post->data()->de         ?? '';
        $ate       = $this->post->data()->ate        ?? '';
        $idUsuario = $this->post->data()->id_usuario ?? 'todos';

        if (empty($de) || empty($ate)) {
            echo '<p style="color:red;padding:20px">Preencha o período antes de buscar.</p>';
            return;
        }

        $periodo         = ['de' => $de, 'ate' => $ate];
        $idUsuarioFiltro = ($idUsuario === 'todos') ? false : (int) $idUsuario;

        $relatorioVendas              = new RelatorioVendasPorPeriodoRepository();
        $vendas                       = $relatorioVendas->agrupamentoDeVendasPorPeriodo($periodo, $idUsuarioFiltro, $this->idEmpresa);
        $totalVendidoPorMeioPagamento = $relatorioVendas->totalVendidoPorMeioDePagamento($periodo, $idUsuarioFiltro, $this->idEmpresa);
        $totalDasVendas               = $relatorioVendas->totalDasVendas($periodo, $idUsuarioFiltro, $this->idEmpresa);

        $this->view('relatorio/vendasPorPeriodo/tabelaVendasPorPeriodo', null, compact(
            'vendas',
            'totalVendidoPorMeioPagamento',
            'totalDasVendas'
        ));
    }

    public function itensDaVendaChamadaAjax(string $codigo): void
    {
        $codigo = out64($codigo) ?: $codigo;

        $relatorioVendas   = new RelatorioVendasPorPeriodoRepository();
        $itens             = $relatorioVendas->itensDaVenda($this->idEmpresa, $codigo);
        $detalhesPagamento = $relatorioVendas->detalhesDePagamentoItensDaVenda($this->idEmpresa, $codigo);

        $this->view('relatorio/vendasPorPeriodo/tabelaItensDaVenda', null, compact(
            'itens',
            'detalhesPagamento'
        ));
    }

    public function gerarXls(string $de = '', string $ate = '', $opcao = false): void
    {
        if (empty($de) || empty($ate)) {
            echo '<script>alert("Preencha o período antes de exportar.");history.back();</script>';
            return;
        }

        $relatorioVendas = new RelatorioVendasPorPeriodoRepository();
        $periodo         = ['de' => $de, 'ate' => $ate];
        $idUsuario       = ($opcao === 'todos') ? false : $opcao;

        $relatorioVendas->gerarRelatioDeVendasPorPeriodoXls($periodo, $idUsuario, $this->idEmpresa);
    }

    public function gerarPDF(string $de = '', string $ate = '', $opcao = false): void
    {
        if (empty($de) || empty($ate)) {
            echo '<script>alert("Preencha o período antes de exportar.");history.back();</script>';
            return;
        }

        $empresa         = (new Empresa())->find($this->idEmpresa);
        $relatorioVendas = new RelatorioVendasPorPeriodoRepository();
        $periodo         = ['de' => $de, 'ate' => $ate];
        $idUsuario       = ($opcao === 'todos') ? false : $opcao;

        $relatorioVendas->gerarRelatioDeVendasPorPeriodoPDF($periodo, $idUsuario, $this->idEmpresa, $empresa);
    }

    public function gerarPdfDeUmaVenda(string $codigo): void
    {
        $codigo = out64($codigo) ?: $codigo;

        $relatorioVendas   = new RelatorioVendasPorPeriodoRepository();
        $itens             = $relatorioVendas->itensDaVenda($this->idEmpresa, $codigo);
        $detalhesPagamento = $relatorioVendas->detalhesDePagamentoItensDaVenda($this->idEmpresa, $codigo);
        $empresa           = (new Empresa())->find($this->idEmpresa);

        $this->view('relatorio/venda/PdfDeUmaVenda', null, compact(
            'itens',
            'detalhesPagamento',
            'empresa'
        ));
    }
}
