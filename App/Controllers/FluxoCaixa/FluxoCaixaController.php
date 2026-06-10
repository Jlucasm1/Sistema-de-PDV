<?php
namespace App\Controllers\FluxoCaixa;

use App\Config\ConfigPerfil;
use App\Models\Usuario;
use App\Rules\Logged;
use App\Rules\PerfilAcessoRule;
use Exception;
use System\Controller\Controller;
use System\Get\Get;
use System\Post\Post;
use System\Session\Session;
use App\Models\FluxoCaixa;

class FluxoCaixaController extends Controller
{
    protected $post;
    protected $get;
    protected $layout;
    protected $idEmpresa;
    protected $idUsuario;
    protected $idPerfilUsuarioLogado;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'default';

        $this->post = new Post();
        $this->get = new Get();
        $this->idEmpresa = (int) Session::get('idEmpresa');
        $this->idUsuario = (int) Session::get('idUsuario');
        $this->idPerfilUsuarioLogado = (int) Session::get('idPerfil');

        $logged = new Logged();
        $logged->isValid();
        (new PerfilAcessoRule())->negaPerfis(
            [ConfigPerfil::vendedor()],
            'home',
            'O fluxo de caixa gerencial não está disponível para vendedores.'
        );
    }

    public function index()
    {
        $usuarios = (new Usuario())->usuarios(
            (int) $this->idEmpresa,
            (int) $this->idUsuario,
            (int) $this->idPerfilUsuarioLogado
        );

        $this->view('fluxoDeCaixa/index', $this->layout, compact('usuarios'));
    }

    # Renderiza a grid do fluxo de caixa. Chamada via ajax
    public function tabelaFluxoDeCaixa()
    {
        $periodo = [
            'de' => $this->post->data()->de,
            'ate' => $this->post->data()->ate
        ];

        $tipoPeriodo = isset($this->post->data()->tipo_periodo) ? $this->post->data()->tipo_periodo : false;
        $idUsuario = isset($this->post->data()->id_usuario) && $this->post->data()->id_usuario !== 'todos'
            ? (int) $this->post->data()->id_usuario
            : null;

        $fluxoCaixa = new FluxoCaixa();
        $fluxoCaixa->setRetirarPDV(is_null($this->post->data()->retirarPDV) ? false : true);
        $retirarPDV = $fluxoCaixa->getRetirarPDV();

        $fluxo = $fluxoCaixa->fluxoDeCaixa($periodo, $this->idEmpresa);
        $fluxoDetalhadoPorMes = $fluxoCaixa->fluxoDeCaixaDetalhadoPorMes($periodo, $tipoPeriodo, $this->idEmpresa);
        $resumoCaixas = $fluxoCaixa->resumoOperacionalDosCaixas($periodo, (int) $this->idEmpresa, $idUsuario);
        $caixasDetalhados = $fluxoCaixa->detalhesDosCaixas($periodo, (int) $this->idEmpresa, $idUsuario);

        $this->view('fluxoDeCaixa/tabelaFluxoDeCaixa', false,
        compact(
            'fluxo',
            'fluxoDetalhadoPorMes',
            'retirarPDV',
            'resumoCaixas',
            'caixasDetalhados'
        ));
    }

    public function modalRegistrarMovimentacao($idFluxo = false)
    {
        $fluxoCaixa = false;

        if ($idFluxo) {
            $fluxoCaixa = new FluxoCaixa();
            $fluxoCaixa = $fluxoCaixa->find($idFluxo);
        }

        $this->view('fluxoDeCaixa/formulario_movimentacao', null, compact('fluxoCaixa'));
    }

    public function save()
    {
        $fluxoCaixa = new FluxoCaixa();
        $dados = (array) $this->post->data();
        $dados['id_empresa'] = $this->idEmpresa;
        $dados['data'] = date('Y-m-d H:i:s', strtotime($dados['data']));
        $dados['valor'] = formataValorMoedaParaGravacao($dados['valor']);
        $dados['tipo_movimento'] = (is_null($dados['tipo_movimento'])) ? 0 : $dados['tipo_movimento'];

        try {
            $fluxoCaixa->save($dados);
            return $this->get->redirectTo("fluxoDeCaixa/index");

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function update()
    {
        $fluxoCaixa = new FluxoCaixa();
        $dados = (array) $this->post->data();
        $dados['id_empresa'] = $this->idEmpresa;
        $dados['data'] = date('Y-m-d H:i:s', strtotime($dados['data']));
        $dados['valor'] = formataValorMoedaParaGravacao($dados['valor']);
        $dados['tipo_movimento'] = (is_null($dados['tipo_movimento'])) ? 0 : $dados['tipo_movimento'];

        try {
            $fluxoCaixa->update($dados, $this->post->data()->id);
            return $this->get->redirectTo("fluxoDeCaixa/index");

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
