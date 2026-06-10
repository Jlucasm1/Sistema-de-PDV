<?php

namespace App\Controllers;

use App\Models\MeioPagamento;
use App\Models\Produto;
use App\Models\Venda;
use App\Services\Fiscal\MokitoImpostoMocker;
use App\Services\Fiscal\NotaFiscalEmitter;
use App\Repositories\VendasEmSessaoRepository;
use App\Rules\AcessoAoTipoDePdv;
use App\Rules\CaixaAbertoRule;
use App\Rules\Logged;
use System\Controller\Controller;
use System\Get\Get;
use System\Post\Post;
use System\Session\Session;

class PdvDiferencialController extends Controller
{
    protected string                  $layout;
    protected int                     $idEmpresa;
    protected int                     $idUsuario;
    protected int                     $idPerfilUsuarioLogado;
    protected Post                    $post;
    protected Get                     $get;
    protected VendasEmSessaoRepository $vendasEmSessaoRepository;
    protected NotaFiscalEmitter       $notaFiscalEmitter;

    public function __construct()
    {
        parent::__construct();
        $this->layout                   = 'default';
        $this->post                     = new Post();
        $this->get                      = new Get();
        $this->idEmpresa                = (int) Session::get('idEmpresa');
        $this->idUsuario                = (int) Session::get('idUsuario');
        $this->idPerfilUsuarioLogado    = (int) Session::get('idPerfil');
        $this->vendasEmSessaoRepository = new VendasEmSessaoRepository();
        $this->notaFiscalEmitter        = new NotaFiscalEmitter(new MokitoImpostoMocker());

        (new Logged())->isValid();
        (new AcessoAoTipoDePdv())->validate();
        (new CaixaAbertoRule())->validate();
    }

    public function index(): void
    {
        $meiosPagamentos = (new MeioPagamento())->all();
        $produtos        = (new Produto())->produtosNoPdv($this->idEmpresa);

        $this->view('pdv/diferencial', $this->layout, compact('meiosPagamentos', 'produtos'));
    }

    /**
     * Salva todas as vendas da sessão no banco
     */
    public function saveVendasViaSession(): void
    {
        if (!$this->vendasEmSessaoRepository->possuiItensNaVenda()) {
            echo json_encode(['status' => false, 'msg' => 'Carrinho vazio']);
            return;
        }

        $itensDaVenda = json_decode($this->vendasEmSessaoRepository->obterProdutosDaMesa(), true);
        if (empty($itensDaVenda)) {
            echo json_encode(['status' => false, 'msg' => 'Carrinho inválido. Recarregue a página e tente novamente.']);
            return;
        }

        $status           = false;
        $notaFiscal       = null;
        $meioPagamento    = (int) $this->post->data()->id_meio_pagamento;
        $dataCompensacao  = null;

        // Boleto tem data de compensação
        if ($meioPagamento === 4) {
            $dataCompensacao = $this->post->data()->data_compensacao ?: null;
        }

        $codigoVenda  = uniqid((string)rand(), true) . date('sddmmYHi');
        $valorRecebido = formataValorMoedaParaGravacao($this->post->data()->valor_recebido ?? '');
        $troco         = formataValorMoedaParaGravacao($this->post->data()->troco ?? '');

        $vendaModel = new Venda();
        $vendaModel->beginTransaction();

        try {
            foreach ($itensDaVenda as $item) {
                $dados = [
                    'id_usuario'        => $this->idUsuario,
                    'id_meio_pagamento' => $meioPagamento,
                    'data_compensacao'  => $dataCompensacao,
                    'id_empresa'        => $this->idEmpresa,
                    'id_produto'        => $item['id'],
                    'preco'             => $item['preco'],
                    'quantidade'        => $item['quantidade'],
                    'valor'             => $item['total'],
                    'codigo_venda'      => $codigoVenda,
                ];

                if ($valorRecebido !== '' && $troco !== '') {
                    $dados['valor_recebido'] = $valorRecebido;
                    $dados['troco']          = $troco;
                }

                $vendaModel->save($dados);

                // Decrementa estoque
                (new Produto())->decrementaQuantidadeProduto((int)$item['id'], (int)$item['quantidade']);
            }

            $vendaModel->commit();
            $notaFiscal = $this->notaFiscalEmitter->emitir(
                $itensDaVenda,
                $codigoVenda,
                $this->idEmpresa,
                $this->idUsuario,
                $meioPagamento
            );
            $this->vendasEmSessaoRepository->limparSessao();
            (new \App\Models\CaixaSessao())->atualizarAtividade($this->idEmpresa, $this->idUsuario);
            $status = true;

        } catch (\Exception $e) {
            $vendaModel->rollback();
            echo json_encode(['status' => false, 'error' => $e->getMessage()]);
            return;
        }

        echo json_encode([
            'status' => $status,
            'nota_fiscal' => $notaFiscal
        ]);
    }

    public function colocarProdutosNaMesa($idProduto): void
    {
        $idProduto = (int) $idProduto;
        if ($idProduto <= 0) {
            echo json_encode(['status' => false, 'message' => 'Produto inválido']);
            return;
        }

        echo $this->vendasEmSessaoRepository->colocarProdutosNaMesa($idProduto);
    }

    public function obterProdutosDaMesa(?string $posicaoProduto = null): void
    {
        echo $this->vendasEmSessaoRepository->obterProdutosDaMesa($posicaoProduto);
    }

    public function alterarAquantidadeDeUmProdutoNaMesa($idProduto, $quantidade): void
    {
        $idProduto = (int) $idProduto;
        $quantidade = (int) $quantidade;

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

    public function retirarProdutoDaMesa($idProduto): void
    {
        $idProduto = (int) $idProduto;
        if ($idProduto <= 0) {
            echo json_encode(['status' => false, 'message' => 'Produto inválido']);
            return;
        }

        $this->vendasEmSessaoRepository->retirarProdutoDaMesa($idProduto);
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

    public function pesquisarProdutoPorNome(?string $nome = null): void
    {
        $nomeDecoded = $nome ? out64($nome) : false;
        $produtos    = (new Produto())->produtosNoPdv($this->idEmpresa, $nomeDecoded);
        $this->view('pdv/produtosAvenda', null, compact('produtos'));
    }

    public function pesquisarProdutoPorCodeDeBarra(?string $codigo = null): void
    {
        $codigoDecoded = $codigo ? out64($codigo) : false;
        $produtos      = (new Produto())->produtosNoPdvFiltrarPorCodigoDeBarra($this->idEmpresa, $codigoDecoded);
        $this->view('pdv/produtosAvenda', null, compact('produtos'));
    }
}
