<?php

namespace App\Controllers;

use App\Models\MeioPagamento;
use App\Models\Produto;
use App\Models\Usuario;
use App\Models\Venda;
use App\Repositories\VendasEmSessaoRepository;
use App\Repositories\VendasDoDiaRepository;
use App\Services\Fiscal\MokitoImpostoMocker;
use App\Services\Fiscal\NotaFiscalEmitter;
use App\Rules\AcessoAoTipoDePdv;
use App\Rules\CaixaAbertoRule;
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
    protected VendasEmSessaoRepository $vendasEmSessaoRepository;
    protected NotaFiscalEmitter $notaFiscalEmitter;

    public function __construct()
    {
        parent::__construct();
        $this->layout                = 'default';
        $this->post                  = new Post();
        $this->get                   = new Get();
        $this->idEmpresa             = (int) Session::get('idEmpresa');
        $this->idUsuario             = (int) Session::get('idUsuario');
        $this->idPerfilUsuarioLogado = (int) Session::get('idPerfil');
        $this->vendasEmSessaoRepository = new VendasEmSessaoRepository();
        $this->notaFiscalEmitter = new NotaFiscalEmitter(new MokitoImpostoMocker());

        (new Logged())->isValid();
        (new AcessoAoTipoDePdv())->validate();
        (new CaixaAbertoRule())->validate();
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
        $produtos        = (new Produto())->produtosNoPdv($this->idEmpresa);

        $this->view('pdv/padrao', $this->layout, compact(
            'vendasGeralDoDia', 'meiosPagamentos', 'usuarios', 'produtos',
            'totalVendasNoDia', 'totalValorVendaPorMeioDePagamentoNoDia', 'totalVendaNoDiaAnterior'
        ));
    }

    public function save(): void
    {
        if (!$this->post->hasPost()) return;

        if ($this->vendasEmSessaoRepository->possuiItensNaVenda()) {
            $this->salvarVendaRapidaComProdutos();
            return;
        }

        $dados               = (array) $this->post->data();
        $dados['id_empresa'] = $this->idEmpresa;
        $dados['valor']      = formataValorMoedaParaGravacao($dados['valor']);

        try {
            (new Venda())->save($dados);
            (new \App\Models\CaixaSessao())->atualizarAtividade($this->idEmpresa, $this->idUsuario);
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

    public function colocarProdutosNaMesa(int $idProduto): void
    {
        $produto = (new Produto())->find($idProduto);
        if (!$produto) {
            echo json_encode(['status' => false, 'message' => 'Produto não encontrado']);
            return;
        }

        if ($produto->ativar_quantidade && $produto->quantidade <= 0) {
            echo json_encode(['status' => false, 'message' => 'Produto sem estoque']);
            return;
        }

        echo $this->vendasEmSessaoRepository->colocarProdutosNaMesa($idProduto);
    }

    public function obterProdutosDaMesa(?string $posicaoProduto = null): void
    {
        echo $this->vendasEmSessaoRepository->obterProdutosDaMesa($posicaoProduto);
    }

    public function alterarAquantidadeDeUmProdutoNaMesa(int $idProduto, int $quantidade): void
    {
        if ($idProduto <= 0 || $quantidade <= 0) {
            echo json_encode(['quantidade_insuficiente' => false, 'message' => 'Parâmetros inválidos']);
            return;
        }

        $produto = (new Produto())->find($idProduto);
        if ($produto && $produto->ativar_quantidade && $quantidade > $produto->quantidade) {
            echo json_encode(['quantidade_insuficiente' => true, 'unidades' => $produto->quantidade]);
            return;
        }

        $this->vendasEmSessaoRepository->alterarAquantidadeDeUmProdutoNaMesa($idProduto, $quantidade);
        echo json_encode(['quantidade_insuficiente' => false]);
    }

    public function retirarProdutoDaMesa(int $idProduto): void
    {
        $this->vendasEmSessaoRepository->retirarProdutoDaMesa($idProduto);
        echo json_encode(['status' => true]);
    }

    public function obterValorTotalDosProdutosNaMesa(): void
    {
        echo $this->vendasEmSessaoRepository->obterValorTotalDosProdutosNaMesa();
    }

    public function calcularTroco(string $valorRecebido): void
    {
        $valor = out64($valorRecebido);
        $valor = str_replace(['R$', '.'], '', $valor ?? '');
        $valor = trim($valor);
        echo $this->vendasEmSessaoRepository->calcularTroco(formataValorMoedaParaGravacao($valor));
    }

    private function salvarVendaRapidaComProdutos(): void
    {
        $itensDaVenda = json_decode($this->vendasEmSessaoRepository->obterProdutosDaMesa(), true);
        if (empty($itensDaVenda)) {
            Session::flash('error', 'Nenhum produto foi selecionado para a venda.');
            $this->get->redirectTo('pdvPadrao');
            return;
        }

        $idUsuarioVenda = (int) ($this->post->data()->id_usuario ?? $this->idUsuario);
        $idMeioPagamento = (int) ($this->post->data()->id_meio_pagamento ?? 0);
        $valorRecebido = formataValorMoedaParaGravacao($this->post->data()->valor_recebido ?? '');
        $troco = formataValorMoedaParaGravacao($this->post->data()->troco ?? '');
        $codigoVenda = uniqid((string) rand(), true) . date('sddmmYHi');

        $vendaModel = new Venda();
        $vendaModel->beginTransaction();

        try {
            foreach ($itensDaVenda as $item) {
                $dados = [
                    'id_usuario' => $idUsuarioVenda,
                    'id_meio_pagamento' => $idMeioPagamento,
                    'id_empresa' => $this->idEmpresa,
                    'id_produto' => $item['id'],
                    'preco' => $item['preco'],
                    'quantidade' => $item['quantidade'],
                    'valor' => $item['total'],
                    'codigo_venda' => $codigoVenda,
                ];

                if ($valorRecebido !== '' && $troco !== '') {
                    $dados['valor_recebido'] = $valorRecebido;
                    $dados['troco'] = $troco;
                }

                $vendaModel->save($dados);
                (new Produto())->decrementaQuantidadeProduto((int) $item['id'], (int) $item['quantidade']);
            }

            $vendaModel->commit();
            $notaFiscal = $this->notaFiscalEmitter->emitir(
                $itensDaVenda,
                $codigoVenda,
                $this->idEmpresa,
                $idUsuarioVenda,
                $idMeioPagamento
            );
            $this->vendasEmSessaoRepository->limparSessao();
            (new \App\Models\CaixaSessao())->atualizarAtividade($this->idEmpresa, $this->idUsuario);
            Session::set('ultima_nota_fiscal_mock', $notaFiscal);
            Session::flash('success', 'Venda rápida registrada com sucesso.');
            $this->get->redirectTo('pdvPadrao');
        } catch (\Exception $e) {
            $vendaModel->rollback();
            Session::flash('error', 'Não foi possível salvar a venda: ' . $e->getMessage());
            $this->get->redirectTo('pdvPadrao');
        }
    }
}

