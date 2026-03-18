<?php

namespace App\Controllers;

use App\Models\Cliente;
use App\Models\Produto;
use App\Repositories\VendasRepository;
use App\Rules\Logged;
use System\Controller\Controller;
use System\Get\Get;
use System\Post\Post;
use System\Session\Session;

class HomeController extends Controller
{
    protected string $layout;
    protected int    $idEmpresa;
    protected int    $idUsuario;
    protected int    $idPerfilUsuarioLogado;

    public function __construct()
    {
        parent::__construct();
        $this->layout               = 'default';
        $this->idEmpresa            = (int) Session::get('idEmpresa');
        $this->idUsuario            = (int) Session::get('idUsuario');
        $this->idPerfilUsuarioLogado = (int) Session::get('idPerfil');

        (new Logged())->isValid();
    }

    public function index(): void
    {
        $vendasRepo = new VendasRepository();

        $faturamentoMes        = $vendasRepo->faturamentoDeVendasNoMes(date('m'), date('Y'), $this->idEmpresa);
        $faturamentoDia        = $vendasRepo->faturamentoDeVendasNoDia(date('d'), date('m'), $this->idEmpresa);

        $mesAnterior = decrementMonthtFromDate(1);
        $ano = (int)date('Y');
        if ($mesAnterior == '12') {
            $ano = (int)date('Y', strtotime('-1 years'));
        }

        $faturamentoMesAnterior = $vendasRepo->faturamentoDeVendasNoMes($mesAnterior, $ano, $this->idEmpresa);
        $faturamentoDiaAnterior = $vendasRepo->faturamentoDeVendasNoDia(
            date('d', strtotime(decrementDaysFromDate(1))),
            date('m'),
            $this->idEmpresa
        );

        $percentualMeiosPagamento         = $vendasRepo->percentualMeiosDePagamento($this->idEmpresa);
        $quantidadeVendasPorDia           = $vendasRepo->quantidadeDeVendasRealizadasPorDia([], $this->idEmpresa);
        $valorVendasPorDia                = $vendasRepo->valorDeVendasRealizadasPorDia([], $this->idEmpresa);
        $totalVendasPorUsuariosNoMes      = $vendasRepo->totalVendasPorUsuariosNoMes($this->idEmpresa, date('m'));
        $produtosMaisVendidosNoMes        = $vendasRepo->produtosMaisVendidosNoMes($this->idEmpresa, date('m'), 6);
        $vendasPorMesNoAno                = $vendasRepo->vendasPorMesNoAno($this->idEmpresa);

        $clientesCadastrados  = (new Cliente())->quantidadeDeClientesCadastrados($this->idEmpresa);
        $produtosCadastrados  = (new Produto())->quantidadeDeProdutosCadastrados($this->idEmpresa);

        $this->view('home/index', $this->layout, compact(
            'faturamentoMes', 'faturamentoDia',
            'faturamentoMesAnterior', 'faturamentoDiaAnterior',
            'percentualMeiosPagamento', 'quantidadeVendasPorDia',
            'valorVendasPorDia', 'totalVendasPorUsuariosNoMes',
            'clientesCadastrados', 'produtosCadastrados',
            'produtosMaisVendidosNoMes', 'vendasPorMesNoAno'
        ));
    }
}
