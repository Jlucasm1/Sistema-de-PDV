<?php

namespace App\Controllers;

use App\Config\ConfigPerfil;
use App\Models\Produto;
use App\Rules\Logged;
use App\Rules\PerfilAcessoRule;
use App\Services\UploadService\UploadFiles;
use System\Controller\Controller;
use System\Get\Get;
use System\Post\Post;
use System\Session\Session;

class ProdutoController extends Controller
{
    protected string $layout;
    protected int    $idEmpresa;
    protected string $diretorioImagem;
    protected Post   $post;
    protected Get    $get;

    public function __construct()
    {
        parent::__construct();
        $this->layout          = 'default';
        $this->post            = new Post();
        $this->get             = new Get();
        $this->idEmpresa       = (int) Session::get('idEmpresa');
        $sharedHost            = filter_var($_ENV['SHARED_HOST'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
        $this->diretorioImagem = $sharedHost ? 'imagem/produtos/' : 'public/imagem/produtos/';
        (new Logged())->isValid();
        (new PerfilAcessoRule())->negaPerfis(
            [ConfigPerfil::vendedor()],
            'home',
            'Produtos não estão disponíveis para vendedores.'
        );
    }

    public function index(): void
    {
        $produto     = new Produto();
        $informacoes = $produto->informacaoesGeraisDosProdutos($this->idEmpresa);
        $produtos    = $produto->produtos($this->idEmpresa);
        $this->view('produto/index', $this->layout, compact('informacoes', 'produtos'));
    }

    public function save(): void
    {
        if (!$this->post->hasPost()) return;

        $produto = new Produto();
        $dados   = (array) $this->post->data();

        if ($this->idEmpresa <= 0) {
            $this->responderProduto(false, 'Empresa não identificada na sessão. Faça login novamente.');
            return;
        }

        if (empty(trim((string) ($dados['nome'] ?? '')))) {
            $this->responderProduto(false, 'Informe o nome do produto.');
            return;
        }

        $dados['id_empresa']        = $this->idEmpresa;
        $dados['preco']             = formataValorMoedaParaGravacao($dados['preco'] ?? '0');
        $dados['mostrar_em_vendas'] = isset($dados['mostrar_em_vendas']) ? 1 : 0;
        $dados['ativar_quantidade'] = isset($dados['ativar_quantidade']) ? 1 : 0;
        $dados['quantidade']        = isset($dados['quantidade']) ? (int)$dados['quantidade'] : 0;

        if (!empty($_FILES['imagem']['name'])) {
            $retorno = uploadImageHelper(new UploadFiles(), $this->diretorioImagem, $_FILES['imagem']);
            if (is_array($retorno)) {
                Session::flash('error', $retorno['error']);
                $this->responderProduto(false, $retorno['error']);
                return;
            }
            $sharedHost      = filter_var($_ENV['SHARED_HOST'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
            $dados['imagem'] = $sharedHost ? "public/{$retorno}" : $retorno;
        }

        // Remove campos que não existem na tabela
        unset($dados['id_empresa_hidden'], $dados['tab'], $dados['_token']);

        try {
            $idProduto = $produto->save($dados);
            if (empty($dados['codigo'])) {
                $codigo = generateRandomCodigoDeBarras($idProduto);
                $produto->update(['codigo' => $codigo], (int)$idProduto);
            }
            $this->responderProduto(true, 'Produto cadastrado com sucesso.');
        } catch (\Exception $e) {
            $this->responderProduto(false, 'Não foi possível cadastrar o produto: ' . $e->getMessage());
        }
    }

    public function update(): void
    {
        if (!$this->post->hasPost()) return;

        $produto      = new Produto();
        $dadosProduto = $produto->find((int)$this->post->data()->id);
        if (!$dadosProduto) return;

        $dados = [
            'nome'              => $this->post->data()->nome ?? $dadosProduto->nome,
            'preco'             => formataValorMoedaParaGravacao($this->post->data()->preco ?? '0'),
            'descricao'         => nl2br($this->post->data()->descricao ?? ''),
            'codigo'            => $this->post->data()->codigo ?? $dadosProduto->codigo,
            'quantidade'        => isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : (int)$dadosProduto->quantidade,
            'mostrar_em_vendas' => isset($this->post->data()->mostrar_em_vendas) ? 1 : 0,
            'ativar_quantidade' => isset($this->post->data()->ativar_quantidade) ? 1 : 0,
        ];

        if (!empty($_FILES['imagem']['name'])) {
            if ($dadosProduto->imagem && file_exists($dadosProduto->imagem)) {
                unlink($dadosProduto->imagem);
            }
            $retorno = uploadImageHelper(new UploadFiles(), $this->diretorioImagem, $_FILES['imagem']);
            if (is_array($retorno)) {
                Session::flash('error', $retorno['error']);
                $this->responderProduto(false, $retorno['error']);
                return;
            }
            $sharedHost      = filter_var($_ENV['SHARED_HOST'] ?? 'false', FILTER_VALIDATE_BOOLEAN);
            $dados['imagem'] = $sharedHost ? "public/{$retorno}" : $retorno;
        }

        try {
            $produto->update($dados, $dadosProduto->id);
            $this->responderProduto(true, 'Produto atualizado com sucesso.');
        } catch (\Exception $e) {
            $this->responderProduto(false, 'Não foi possível atualizar o produto: ' . $e->getMessage());
        }
    }

    public function modalFormulario(?string $idProduto = null): void
    {
        $produto = $idProduto ? (new Produto())->find((int)$idProduto) : null;
        $this->view('produto/formulario', null, compact('produto'));
    }

    public function pesquisarProdutoPorNome(?string $nome = null): void
    {
        $nomeDecoded = $nome ? out64($nome) : false;
        $produtos    = (new Produto())->produtos($this->idEmpresa, $nomeDecoded);
        $this->view('produto/produtos', null, compact('produtos', 'nomeDecoded'));
    }

    public function pesquisarProdutoPorCodigoDeBarras(?string $codigo = null): void
    {
        $codigoDecoded = $codigo ? out64($codigo) : false;
        $produtos      = (new Produto())->getBy($this->idEmpresa, 'codigo', $codigoDecoded);
        $this->view('produto/produtos', null, compact('produtos', 'codigoDecoded'));
    }

    public function excluirProduto(int $idProduto): void
    {
        try {
            (new Produto())->update(['deleted_at' => timestamp()], $idProduto);
            echo json_encode(['deletado' => true]);
        } catch (\Exception $e) {
            echo json_encode(['deletado' => false, 'error' => $e->getMessage()]);
        }
    }

    private function responderProduto(bool $status, string $message): void
    {
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['status' => $status, 'message' => $message], JSON_UNESCAPED_UNICODE);
            return;
        }

        Session::flash($status ? 'success' : 'error', $message);
        $this->get->redirectTo('produto');
    }

    private function isAjaxRequest(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }
}
