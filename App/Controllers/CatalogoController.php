<?php

namespace App\Controllers;

use App\Models\CatalogoPermissao;
use App\Models\ProdutoHistorico;
use App\Repositories\CatalogoProdutoRepository;
use App\Rules\CatalogoAcesso;
use App\Rules\Logged;
use App\Services\Catalogo\PrecoCalculator;
use System\Controller\Controller;
use System\Get\Get;
use System\Session\Session;
use System\Session\Token;

/**
 * Consulta comercial, simulador de descontos e gerenciamento do catálogo.
 * Não registra vendas, não mexe em estoque, caixa ou pedidos.
 */
class CatalogoController extends Controller
{
    private const LAYOUT = 'default';

    private CatalogoAcesso $acesso;
    private CatalogoProdutoRepository $repo;
    private int $idEmpresa;
    private int $idUsuario;

    public function __construct()
    {
        parent::__construct();
        (new Logged())->isValid();
        $this->acesso    = new CatalogoAcesso();
        $this->repo      = new CatalogoProdutoRepository();
        $this->idEmpresa = $this->acesso->idEmpresa();
        $this->idUsuario = $this->acesso->idUsuario();
    }

    public function index(): void
    {
        $podeGerenciar = $this->acesso->podeGerenciarCatalogo();
        $this->view('catalogo/consulta', self::LAYOUT, compact('podeGerenciar'));
    }

    public function buscar(): void
    {
        $termo    = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $produtos = $this->repo->buscar($this->idEmpresa, $termo, 30);
        $verCusto = $this->acesso->podeGerenciarCatalogo();

        $this->json(['status' => true, 'produtos' => array_map(fn($p) => $this->fichaDoProduto($p, $verCusto), $produtos)]);
    }

    public function ficha($idProduto): void
    {
        $produto = $this->repo->obter($this->idEmpresa, (int) $idProduto);
        if (!$produto) {
            $this->json(['status' => false, 'message' => 'Produto não encontrado.'], 404);
        }
        $this->json(['status' => true, 'produto' => $this->fichaDoProduto($produto, $this->acesso->podeGerenciarCatalogo())]);
    }

    public function simular(): void
    {
        $this->exigeCsrf();

        $produto = $this->repo->obter($this->idEmpresa, (int) ($_POST['produto_id'] ?? 0));
        if (!$produto) {
            $this->json(['status' => false, 'message' => 'Produto não encontrado.'], 404);
        }

        $oficial = (int) PrecoCalculator::paraCentavos((string) $produto->preco);
        $minimo  = (int) PrecoCalculator::paraCentavos((string) $produto->preco_minimo);
        $custo   = (int) PrecoCalculator::paraCentavos((string) ($produto->preco_compra ?? '0'));

        $resultado = PrecoCalculator::simular(
            $oficial,
            $minimo,
            $_POST['quantidade'] ?? 1,
            (string) ($_POST['modo'] ?? PrecoCalculator::MODO_NENHUM),
            $_POST['valor'] ?? null,
            $custo
        );

        if (!$resultado['ok']) {
            $this->json(['status' => false, 'message' => $resultado['erro']]);
        }

        // Margem e lucro revelam o custo: só para quem gerencia o catálogo
        if (!$this->acesso->podeGerenciarCatalogo()) {
            unset($resultado['margem_percentual'], $resultado['lucro_unitario']);
        }

        $this->json(['status' => true, 'simulacao' => $resultado]);
    }

    public function gerenciar(): void
    {
        $this->exigeGerenciar(false);

        $termo    = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $produtos = $this->repo->buscar($this->idEmpresa, $termo, 200);
        $podePrecoOficial   = $this->acesso->podeAlterarPrecoOficial();
        $podeConcederAcesso = $this->acesso->podeConcederPermissao();

        $this->view('catalogo/gerenciar', self::LAYOUT, compact('produtos', 'termo', 'podePrecoOficial', 'podeConcederAcesso'));
    }

    public function formulario($idProduto = null): void
    {
        $this->exigeGerenciar(true);

        $produto = $idProduto ? $this->repo->obter($this->idEmpresa, (int) $idProduto) : null;
        if ($idProduto && !$produto) {
            $this->json(['status' => false, 'message' => 'Produto não encontrado.'], 404);
        }

        $podePrecoOficial = $this->acesso->podeAlterarPrecoOficial();
        $this->view('catalogo/formulario', null, compact('produto', 'podePrecoOficial'));
    }

    public function salvar(): void
    {
        $this->exigeCsrf();
        $this->exigeGerenciar(true);

        $idProduto        = (int) ($_POST['id'] ?? 0);
        $podePrecoOficial = $this->acesso->podeAlterarPrecoOficial();

        $resultado = $idProduto > 0
            ? $this->repo->atualizar($this->idEmpresa, $this->idUsuario, $idProduto, $_POST, $podePrecoOficial)
            : $this->repo->criar($this->idEmpresa, $this->idUsuario, $_POST, true);

        if (!$resultado['ok']) {
            $this->json(['status' => false, 'message' => $resultado['erro']], 422);
        }

        $this->json(['status' => true, 'message' => $idProduto > 0 ? 'Produto atualizado com sucesso.' : 'Produto cadastrado com sucesso.']);
    }

    public function excluir(): void
    {
        $this->exigeCsrf();
        $this->exigeGerenciar(true);

        $ok = $this->repo->excluir($this->idEmpresa, $this->idUsuario, (int) ($_POST['id'] ?? 0));
        if (!$ok) {
            $this->json(['status' => false, 'message' => 'Produto não encontrado ou não pôde ser excluído.'], 422);
        }

        $this->json(['status' => true, 'message' => 'Produto excluído.']);
    }

    public function historico($idProduto = null): void
    {
        $this->exigeGerenciar(false);

        $idProduto = $idProduto ? (int) $idProduto : null;
        $registros = (new ProdutoHistorico())->listar($this->idEmpresa, $idProduto, 300);

        $this->view('catalogo/historico', self::LAYOUT, compact('registros', 'idProduto'));
    }

    public function permissao(): void
    {
        $this->exigeConcederPermissao(false);

        $permissao = new CatalogoPermissao();
        $titular   = $permissao->titularDaEmpresa($this->idEmpresa);
        $usuarios  = $permissao->usuariosElegiveis($this->idEmpresa);

        $this->view('catalogo/permissao', self::LAYOUT, compact('titular', 'usuarios'));
    }

    public function conceder(): void
    {
        $this->exigeCsrf();
        $this->exigeConcederPermissao(true);

        $idAlvo   = (int) ($_POST['id_usuario'] ?? 0);
        $elegivel = array_filter(
            (new CatalogoPermissao())->usuariosElegiveis($this->idEmpresa),
            fn($u) => (int) $u->id === $idAlvo
        );

        if (!$elegivel) {
            $this->json(['status' => false, 'message' => 'Usuário inválido para esta empresa.'], 422);
        }

        (new CatalogoPermissao())->conceder($this->idEmpresa, $idAlvo, $this->idUsuario);
        $this->json(['status' => true, 'message' => 'Permissão concedida. O usuário anterior (se houver) foi substituído.']);
    }

    public function revogar(): void
    {
        $this->exigeCsrf();
        $this->exigeConcederPermissao(true);

        (new CatalogoPermissao())->revogar($this->idEmpresa);
        $this->json(['status' => true, 'message' => 'Permissão revogada.']);
    }

    private function fichaDoProduto(object $produto, bool $verCusto = false): array
    {
        $oficial = (int) PrecoCalculator::paraCentavos((string) $produto->preco);
        $minimo  = (int) PrecoCalculator::paraCentavos((string) $produto->preco_minimo);
        $custo   = (int) PrecoCalculator::paraCentavos((string) ($produto->preco_compra ?? '0'));

        $ficha = [
            'id'                         => (int) $produto->id,
            'nome'                       => $produto->nome,
            'descricao'                  => $produto->descricao,
            'codigo_interno'             => $produto->codigo_interno,
            'codigo_barras'              => $produto->codigo,
            'preco_oficial'              => PrecoCalculator::formatarCentavos($oficial),
            'preco_minimo'               => PrecoCalculator::formatarCentavos($minimo),
            'desconto_maximo_reais'      => PrecoCalculator::formatarCentavos(PrecoCalculator::descontoMaximoCentavos($oficial, $minimo)),
            'desconto_maximo_percentual' => PrecoCalculator::formatarPercentual(PrecoCalculator::descontoMaximoCentesimos($oficial, $minimo)),
            'sem_preco_oficial'          => $oficial <= 0,
            'sem_preco_minimo'           => $minimo <= 0,
            // Alerta sem revelar o valor: o mínimo autorizado está abaixo ou perto do custo
            'minimo_custo_nivel'         => $minimo > 0 ? PrecoCalculator::avaliarCusto($minimo, $custo)['nivel'] : PrecoCalculator::CUSTO_SEM,
        ];

        if ($verCusto) {
            $ficha['custo']             = $custo > 0 ? PrecoCalculator::formatarCentavos($custo) : null;
            $ficha['margem_oficial']    = PrecoCalculator::avaliarCusto($oficial, $custo)['margem_percentual'];
            $ficha['margem_minimo']     = $minimo > 0 ? PrecoCalculator::avaliarCusto($minimo, $custo)['margem_percentual'] : null;
        }

        return $ficha;
    }

    private function exigeGerenciar(bool $json): void
    {
        if ($this->acesso->podeGerenciarCatalogo()) {
            return;
        }
        $this->negar($json, 'Você não tem permissão para gerenciar o catálogo de produtos.');
    }

    private function exigeConcederPermissao(bool $json): void
    {
        if ($this->acesso->podeConcederPermissao()) {
            return;
        }
        $this->negar($json, 'Somente administradores podem conceder esta permissão.');
    }

    private function negar(bool $json, string $mensagem): void
    {
        if ($json) {
            $this->json(['status' => false, 'message' => $mensagem], 403);
        }
        Session::flash('error', $mensagem);
        (new Get())->redirectTo('home');
        exit;
    }

    private function exigeCsrf(): void
    {
        $token = $_POST['_token'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($token) || !Token::validate($token)) {
            $this->json(['status' => false, 'message' => 'Requisição inválida. Recarregue a página e tente novamente.'], 403);
        }
    }

    private function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }
}
