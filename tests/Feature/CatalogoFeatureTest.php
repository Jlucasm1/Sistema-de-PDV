<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CatalogoPermissao;
use App\Repositories\CatalogoProdutoRepository;
use App\Rules\CatalogoAcesso;
use PDO;
use PHPUnit\Framework\TestCase;
use System\Database\Native;

/**
 * Integração com o MySQL do ambiente Docker e com o servidor HTTP local.
 * Usa empresas/usuários de teste (ids 9001+) e remove tudo ao final.
 */
class CatalogoFeatureTest extends TestCase
{
    private const EMP_A = 9001;
    private const EMP_B = 9002;
    private const U_GERENCIADOR = 9101;
    private const U_VENDEDOR    = 9102;
    private const U_GERENTE     = 9103;
    private const U_VENDEDOR_B  = 9201;
    private const SENHA = 'senha-teste-9';

    private static PDO $pdo;
    private static string $base = 'http://localhost';

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../vendor/autoload.php';
        self::$pdo = Native::connect();
        self::limpar();

        self::$pdo->exec("INSERT INTO empresas (id, nome, email, id_segmento) VALUES
            (" . self::EMP_A . ", 'Teste A', 'a@teste.local', 8), (" . self::EMP_B . ", 'Teste B', 'b@teste.local', 8)");

        $salt  = 'pdvmaster@2024#secure_salt_key';
        $hash  = sha1(self::SENHA . $salt . strrev($salt));
        $ins   = self::$pdo->prepare("INSERT INTO usuarios (id, id_empresa, nome, email, password, id_sexo, id_perfil, status) VALUES (?, ?, ?, ?, ?, 1, ?, 1)");
        $ins->execute([self::U_GERENCIADOR, self::EMP_A, 'Gerenciador', 'gerenciador@teste.local', $hash, 4]);
        $ins->execute([self::U_VENDEDOR, self::EMP_A, 'Vendedor', 'vendedor@teste.local', $hash, 4]);
        $ins->execute([self::U_GERENTE, self::EMP_A, 'Gerente', 'gerente@teste.local', $hash, 5]);
        $ins->execute([self::U_VENDEDOR_B, self::EMP_B, 'Vendedor B', 'vendedorb@teste.local', $hash, 4]);

        (new CatalogoPermissao())->conceder(self::EMP_A, self::U_GERENCIADOR, self::U_GERENTE);
    }

    public static function tearDownAfterClass(): void
    {
        self::limpar();
    }

    private static function limpar(): void
    {
        $ids = implode(',', [self::EMP_A, self::EMP_B]);
        self::$pdo->exec("DELETE FROM produtos_historico WHERE id_empresa IN ($ids)");
        self::$pdo->exec("DELETE FROM catalogo_permissoes WHERE id_empresa IN ($ids)");
        self::$pdo->exec("DELETE FROM log_acessos WHERE id_empresa IN ($ids)");
        self::$pdo->exec("DELETE FROM produtos WHERE id_empresa IN ($ids)");
        self::$pdo->exec("DELETE FROM usuarios WHERE id_empresa IN ($ids)");
        self::$pdo->exec("DELETE FROM empresas WHERE id IN ($ids)");
    }

    private function repo(): CatalogoProdutoRepository
    {
        return new CatalogoProdutoRepository();
    }

    private function contagensProtegidas(): array
    {
        $out = [];
        foreach (['vendas', 'pedidos', 'produtos_pedidos', 'fluxo_caixa'] as $t) {
            $out[$t] = (int) self::$pdo->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
        }
        $out['estoque'] = self::$pdo->query("SELECT COALESCE(SUM(quantidade),0), COALESCE(SUM(ativar_quantidade),0), COUNT(*) FROM produtos WHERE id_empresa NOT IN (" . self::EMP_A . "," . self::EMP_B . ")")->fetch(PDO::FETCH_NUM);
        return $out;
    }

    private function criarComoAdmin(string $nome, string $preco = '150,00', string $minimo = '130,00', array $extra = []): int
    {
        $r = $this->repo()->criar(self::EMP_A, self::U_GERENTE, array_merge([
            'nome' => $nome, 'descricao' => 'Descrição ' . $nome, 'codigo_interno' => null, 'codigo' => null,
            'preco' => $preco, 'preco_minimo' => $minimo,
        ], $extra), true);
        $this->assertTrue($r['ok'], $r['erro'] ?? '');
        return $r['id'];
    }

    private function http(string $metodo, string $caminho, ?string $cookie, array $post = []): array
    {
        $ch = curl_init(self::$base . $caminho);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CUSTOMREQUEST => $metodo,
            CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest'],
        ]);
        if ($cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        if ($metodo === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        $resp = curl_exec($ch);
        $this->assertNotFalse($resp, 'Servidor HTTP indisponível: ' . curl_error($ch));
        $status  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hsize   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($resp, 0, $hsize);
        $body    = substr($resp, $hsize);
        curl_close($ch);
        preg_match_all('/^Set-Cookie:\s*([^=]+=[^;]*)/mi', $headers, $m);
        return ['status' => $status, 'body' => $body, 'cookies' => $m[1] ?? [], 'json' => json_decode($body, true)];
    }

    private function login(string $email): array
    {
        $pagina = $this->http('GET', '/login', null);
        $cookie = implode('; ', $pagina['cookies']);
        $r = $this->http('POST', '/login/logar', $cookie, ['email' => $email, 'password' => self::SENHA]);
        $cookie = implode('; ', array_merge($pagina['cookies'], $r['cookies']));
        $home = $this->http('GET', '/catalogo', $cookie);
        $this->assertSame(200, $home['status'], "Login falhou para {$email}");
        preg_match('/var TOKEN = "([a-f0-9]+)"/', $home['body'], $t);
        $this->assertNotEmpty($t[1] ?? null, 'Token CSRF não encontrado na página.');
        return ['cookie' => $cookie, 'token' => $t[1]];
    }

    public function testCadastroPersistenciaEPesquisas(): void
    {
        $id = $this->criarComoAdmin('Ração Animal 20 kg', '150,00', '130,00', ['codigo_interno' => 'RA-20', 'codigo' => '7890000000017']);

        $row = self::$pdo->query("SELECT * FROM produtos WHERE id = {$id}")->fetch(PDO::FETCH_OBJ);
        $this->assertSame('150.00', $row->preco);
        $this->assertSame('130.00', $row->preco_minimo);
        $this->assertNull($row->deleted_at);

        $repo = $this->repo();
        $this->assertSame($id, (int) $repo->buscar(self::EMP_A, 'ração animal')[0]->id);
        $this->assertSame($id, (int) $repo->buscar(self::EMP_A, 'Descrição Ração')[0]->id);
        $this->assertSame($id, (int) $repo->buscar(self::EMP_A, 'RA-20')[0]->id);
        $this->assertSame($id, (int) $repo->buscar(self::EMP_A, '7890000000017')[0]->id);
        $this->assertCount(0, $repo->buscar(self::EMP_A, 'inexistente-xyz'));
        $this->assertCount(0, $repo->buscar(self::EMP_A, "'; DROP TABLE produtos; --"));
        $this->assertSame(1, (int) self::$pdo->query("SELECT COUNT(*) FROM produtos WHERE id = {$id}")->fetchColumn());
    }

    public function testEdicaoDeNomeDescricaoEPrecoMinimoGeraHistoricoEPrecoOficialFicaProtegido(): void
    {
        $id = $this->criarComoAdmin('Sal Mineral', '100,00', '80,00');
        $antes = $this->contagensProtegidas();

        $r = $this->repo()->atualizar(self::EMP_A, self::U_GERENCIADOR, $id, [
            'nome' => 'Sal Mineral 25 kg', 'descricao' => 'Nova descrição', 'codigo_interno' => 'SM-25',
            'codigo' => '789111', 'preco_minimo' => '85,50', 'preco' => '1,00',
        ], false);
        $this->assertTrue($r['ok'], $r['erro'] ?? '');

        $row = self::$pdo->query("SELECT * FROM produtos WHERE id = {$id}")->fetch(PDO::FETCH_OBJ);
        $this->assertSame('Sal Mineral 25 kg', $row->nome);
        $this->assertSame('85.50', $row->preco_minimo);
        $this->assertSame('100.00', $row->preco, 'Preço oficial não pode mudar sem autorização.');

        $hist = self::$pdo->query("SELECT campo, valor_anterior, valor_novo, id_usuario, id_empresa FROM produtos_historico WHERE id_produto = {$id} AND operacao = 'edicao' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $campos = array_column($hist, 'campo');
        foreach (['nome', 'descricao', 'codigo_interno', 'codigo', 'preco_minimo'] as $c) {
            $this->assertContains($c, $campos);
        }
        $this->assertNotContains('preco', $campos);
        $min = $hist[array_search('preco_minimo', $campos, true)];
        $this->assertSame('80.00', $min['valor_anterior']);
        $this->assertSame('85.50', $min['valor_novo']);
        $this->assertSame(self::U_GERENCIADOR, (int) $min['id_usuario']);
        $this->assertSame(self::EMP_A, (int) $min['id_empresa']);

        $this->assertSame($antes, $this->contagensProtegidas());
    }

    public function testAdministradorAlteraPrecoOficialEMinimoNaoPodeExcederOficial(): void
    {
        $id = $this->criarComoAdmin('Milho 60 kg', '100,00', '80,00');

        $ok = $this->repo()->atualizar(self::EMP_A, self::U_GERENTE, $id, ['nome' => 'Milho 60 kg', 'preco' => '120,00', 'preco_minimo' => '90,00'], true);
        $this->assertTrue($ok['ok']);
        $this->assertSame('120.00', self::$pdo->query("SELECT preco FROM produtos WHERE id = {$id}")->fetchColumn());

        $ruim = $this->repo()->atualizar(self::EMP_A, self::U_GERENCIADOR, $id, ['nome' => 'Milho 60 kg', 'preco_minimo' => '130,00'], false);
        $this->assertFalse($ruim['ok']);
        $this->assertStringContainsString('maior que o preço oficial', $ruim['erro']);
    }

    public function testGerenciadorPodeDefinirPrecoOficialAoCriarProduto(): void
    {
        $r = $this->repo()->criar(self::EMP_A, self::U_GERENCIADOR, ['nome' => 'Produto Novo', 'preco' => '99,00', 'preco_minimo' => '10,00'], true);
        $this->assertTrue($r['ok']);
        $row = self::$pdo->query("SELECT preco, mostrar_em_vendas, quantidade, ativar_quantidade FROM produtos WHERE id = {$r['id']}")->fetch(PDO::FETCH_OBJ);
        $this->assertSame('99.00', $row->preco);
        $this->assertSame(1, (int) $row->mostrar_em_vendas);
        $this->assertSame(0, (int) $row->quantidade);
        $this->assertSame(0, (int) $row->ativar_quantidade);
    }

    public function testExclusaoLogicaPreservaHistoricoEVendas(): void
    {
        $id = $this->criarComoAdmin('Produto Excluir');
        $antes = $this->contagensProtegidas();

        $this->assertTrue($this->repo()->excluir(self::EMP_A, self::U_GERENCIADOR, $id));

        $row = self::$pdo->query("SELECT deleted_at FROM produtos WHERE id = {$id}")->fetch(PDO::FETCH_OBJ);
        $this->assertNotNull($row, 'Registro deve continuar existindo (exclusão lógica).');
        $this->assertNotNull($row->deleted_at);
        $this->assertCount(0, $this->repo()->buscar(self::EMP_A, 'Produto Excluir'));
        $this->assertNull($this->repo()->obter(self::EMP_A, $id));

        $ops = self::$pdo->query("SELECT operacao FROM produtos_historico WHERE id_produto = {$id}")->fetchAll(PDO::FETCH_COLUMN);
        $this->assertContains('cadastro', $ops);
        $this->assertContains('exclusao', $ops);
        $this->assertSame($antes, $this->contagensProtegidas());
    }

    public function testIsolamentoEntreEmpresas(): void
    {
        $id = $this->criarComoAdmin('Produto Exclusivo A');

        $this->assertCount(0, $this->repo()->buscar(self::EMP_B, 'Exclusivo'));
        $this->assertNull($this->repo()->obter(self::EMP_B, $id));
        $this->assertFalse($this->repo()->atualizar(self::EMP_B, self::U_VENDEDOR_B, $id, ['nome' => 'Hack'], false)['ok']);
        $this->assertFalse($this->repo()->excluir(self::EMP_B, self::U_VENDEDOR_B, $id));
        $this->assertSame('Produto Exclusivo A', self::$pdo->query("SELECT nome FROM produtos WHERE id = {$id}")->fetchColumn());

        $outro = $this->repo()->criar(self::EMP_B, self::U_VENDEDOR_B, ['nome' => 'Produto B'], false);
        $this->assertTrue($outro['ok']);
        $this->assertCount(0, $this->repo()->buscar(self::EMP_A, 'Produto B'));
    }

    public function testCodigosDuplicadosNaMesmaEmpresaSaoRejeitados(): void
    {
        $this->criarComoAdmin('Com Codigo', '10,00', '5,00', ['codigo_interno' => 'DUP-1', 'codigo' => '555000']);
        $a = $this->repo()->criar(self::EMP_A, self::U_GERENTE, ['nome' => 'Outro', 'codigo_interno' => 'DUP-1'], true);
        $b = $this->repo()->criar(self::EMP_A, self::U_GERENTE, ['nome' => 'Outro', 'codigo' => '555000'], true);
        $this->assertFalse($a['ok']);
        $this->assertFalse($b['ok']);
    }

    public function testRegrasDePermissaoPorUsuarioEPerfil(): void
    {
        $gerenciador = new CatalogoAcesso(self::U_GERENCIADOR, 4, self::EMP_A);
        $vendedor    = new CatalogoAcesso(self::U_VENDEDOR, 4, self::EMP_A);
        $gerente     = new CatalogoAcesso(self::U_GERENTE, 5, self::EMP_A);
        $admin       = new CatalogoAcesso(1, 2, self::EMP_A);
        $mesmoIdOutraEmpresa = new CatalogoAcesso(self::U_GERENCIADOR, 4, self::EMP_B);

        $this->assertTrue($gerenciador->podeGerenciarCatalogo());
        $this->assertFalse($gerenciador->podeAlterarPrecoOficial());
        $this->assertFalse($gerenciador->podeConcederPermissao());
        $this->assertFalse($vendedor->podeGerenciarCatalogo());
        $this->assertTrue($gerente->podeGerenciarCatalogo());
        $this->assertTrue($gerente->podeAlterarPrecoOficial());
        $this->assertFalse($gerente->podeConcederPermissao());
        $this->assertTrue($admin->podeConcederPermissao());
        $this->assertFalse($mesmoIdOutraEmpresa->podeGerenciarCatalogo());
    }

    public function testApenasUmUsuarioPorEmpresaPodeTerAPermissao(): void
    {
        $perm = new CatalogoPermissao();
        $perm->conceder(self::EMP_A, self::U_VENDEDOR, self::U_GERENTE);
        $this->assertTrue($perm->usuarioPossui(self::EMP_A, self::U_VENDEDOR));
        $this->assertFalse($perm->usuarioPossui(self::EMP_A, self::U_GERENCIADOR));
        $this->assertSame(1, (int) self::$pdo->query("SELECT COUNT(*) FROM catalogo_permissoes WHERE id_empresa = " . self::EMP_A)->fetchColumn());

        $this->expectException(\PDOException::class);
        self::$pdo->exec("INSERT INTO catalogo_permissoes (id_empresa, id_usuario, concedido_por) VALUES (" . self::EMP_A . ", " . self::U_GERENCIADOR . ", " . self::U_GERENTE . ")");
    }

    protected function tearDown(): void
    {
        (new CatalogoPermissao())->conceder(self::EMP_A, self::U_GERENCIADOR, self::U_GERENTE);
    }

    public function testHttpVendedorComumPesquisaESimulaMasNaoAlteraCatalogo(): void
    {
        $id = $this->criarComoAdmin('Ração HTTP', '150,00', '130,00', ['codigo_interno' => 'HTTP-1']);
        $s  = $this->login('vendedor@teste.local');

        $busca = $this->http('GET', '/catalogo/buscar?q=HTTP-1', $s['cookie']);
        $this->assertSame(200, $busca['status']);
        $this->assertSame($id, $busca['json']['produtos'][0]['id']);
        $this->assertSame('130.00', $busca['json']['produtos'][0]['preco_minimo']);
        $this->assertSame('20.00', $busca['json']['produtos'][0]['desconto_maximo_reais']);
        $this->assertSame('13.33', $busca['json']['produtos'][0]['desconto_maximo_percentual']);
        $this->assertArrayNotHasKey('preco_compra', $busca['json']['produtos'][0]);

        $sim = $this->http('POST', '/catalogo/simular', $s['cookie'], ['_token' => $s['token'], 'produto_id' => $id, 'quantidade' => 5, 'modo' => 'reais', 'valor' => '15,00']);
        $this->assertSame('675.00', $sim['json']['simulacao']['valor_total']);
        $this->assertSame('autorizado', $sim['json']['simulacao']['status']);

        $bloq = $this->http('POST', '/catalogo/simular', $s['cookie'], ['_token' => $s['token'], 'produto_id' => $id, 'quantidade' => 1, 'modo' => 'preco_final', 'valor' => '100,00']);
        $this->assertSame('bloqueado', $bloq['json']['simulacao']['status']);
        $this->assertSame('30.00', $bloq['json']['simulacao']['excesso_unitario']);

        $semCsrf = $this->http('POST', '/catalogo/simular', $s['cookie'], ['produto_id' => $id, 'quantidade' => 1]);
        $this->assertSame(403, $semCsrf['status']);

        foreach ([
            ['POST', '/catalogo/salvar', ['_token' => $s['token'], 'nome' => 'Invasor']],
            ['POST', '/catalogo/salvar', ['_token' => $s['token'], 'id' => $id, 'nome' => 'Invasor', 'preco_minimo' => '1,00']],
            ['POST', '/catalogo/excluir', ['_token' => $s['token'], 'id' => $id]],
            ['GET', '/catalogo/formulario/' . $id, []],
            ['POST', '/catalogo/conceder', ['_token' => $s['token'], 'id_usuario' => self::U_VENDEDOR]],
            ['POST', '/catalogo/revogar', ['_token' => $s['token']]],
        ] as [$m, $url, $dados]) {
            $r = $this->http($m, $url, $s['cookie'], $dados);
            $this->assertSame(403, $r['status'], "{$m} {$url} deveria ser negado");
        }
        $paginas = $this->http('GET', '/catalogo/gerenciar', $s['cookie']);
        $this->assertNotSame(200, $paginas['status']);

        $row = self::$pdo->query("SELECT nome, preco_minimo, deleted_at FROM produtos WHERE id = {$id}")->fetch(PDO::FETCH_OBJ);
        $this->assertSame('Ração HTTP', $row->nome);
        $this->assertSame('130.00', $row->preco_minimo);
        $this->assertNull($row->deleted_at);

        $outraEmpresa = $this->login('vendedorb@teste.local');
        $this->assertCount(0, $this->http('GET', '/catalogo/buscar?q=HTTP-1', $outraEmpresa['cookie'])['json']['produtos']);
        $this->assertSame(404, $this->http('POST', '/catalogo/simular', $outraEmpresa['cookie'], ['_token' => $outraEmpresa['token'], 'produto_id' => $id, 'quantidade' => 1])['status']);
    }

    public function testHttpUsuarioAutorizadoDefinePrecoAoCadastrarMasNaoAlteraDepois(): void
    {
        $s = $this->login('gerenciador@teste.local');

        $formNovo = $this->http('GET', '/catalogo/formulario', $s['cookie']);
        $this->assertSame(200, $formNovo['status']);
        $this->assertStringContainsString('name="preco"', $formNovo['body']);

        $novo = $this->http('POST', '/catalogo/salvar', $s['cookie'], ['_token' => $s['token'], 'nome' => 'Via HTTP', 'descricao' => 'd', 'codigo_interno' => 'HT-9', 'codigo' => '777', 'preco' => '500,00', 'preco_minimo' => '20,00']);
        $this->assertSame(200, $novo['status'], $novo['body']);
        $id = (int) self::$pdo->query("SELECT id FROM produtos WHERE id_empresa = " . self::EMP_A . " AND nome = 'Via HTTP'")->fetchColumn();
        $this->assertGreaterThan(0, $id);
        $this->assertSame('500.00', self::$pdo->query("SELECT preco FROM produtos WHERE id = {$id}")->fetchColumn());

        $formEdicao = $this->http('GET', '/catalogo/formulario/' . $id, $s['cookie']);
        $this->assertSame(200, $formEdicao['status']);
        $this->assertStringNotContainsString('name="preco"', $formEdicao['body']);
        $this->assertStringContainsString('Somente gerente ou administrador', $formEdicao['body']);

        $ed = $this->http('POST', '/catalogo/salvar', $s['cookie'], ['_token' => $s['token'], 'id' => $id, 'nome' => 'Via HTTP 2', 'preco' => '1,00', 'preco_minimo' => '25,00']);
        $this->assertSame(200, $ed['status'], $ed['body']);
        $row = self::$pdo->query("SELECT nome, preco, preco_minimo FROM produtos WHERE id = {$id}")->fetch(PDO::FETCH_OBJ);
        $this->assertSame('Via HTTP 2', $row->nome);
        $this->assertSame('500.00', $row->preco);
        $this->assertSame('25.00', $row->preco_minimo);

        $semCsrf = $this->http('POST', '/catalogo/salvar', $s['cookie'], ['nome' => 'x']);
        $this->assertSame(403, $semCsrf['status']);

        $this->assertSame(200, $this->http('GET', '/catalogo/gerenciar', $s['cookie'])['status']);
        $this->assertSame(403, $this->http('POST', '/catalogo/conceder', $s['cookie'], ['_token' => $s['token'], 'id_usuario' => self::U_GERENCIADOR])['status']);

        $del = $this->http('POST', '/catalogo/excluir', $s['cookie'], ['_token' => $s['token'], 'id' => $id]);
        $this->assertSame(200, $del['status']);
        $this->assertNotNull(self::$pdo->query("SELECT deleted_at FROM produtos WHERE id = {$id}")->fetchColumn());
        $this->assertGreaterThanOrEqual(3, (int) self::$pdo->query("SELECT COUNT(*) FROM produtos_historico WHERE id_produto = {$id}")->fetchColumn());
    }

    public function testHttpGerenteAlteraPrecoOficial(): void
    {
        $id = $this->criarComoAdmin('Preco Gerente', '100,00', '80,00');
        $s  = $this->login('gerente@teste.local');

        $r = $this->http('POST', '/catalogo/salvar', $s['cookie'], ['_token' => $s['token'], 'id' => $id, 'nome' => 'Preco Gerente', 'preco' => '110,00', 'preco_minimo' => '90,00']);
        $this->assertSame(200, $r['status'], $r['body']);
        $this->assertSame('110.00', self::$pdo->query("SELECT preco FROM produtos WHERE id = {$id}")->fetchColumn());
        $this->assertSame(1, (int) self::$pdo->query("SELECT COUNT(*) FROM produtos_historico WHERE id_produto = {$id} AND campo = 'preco' AND valor_anterior = '100.00' AND valor_novo = '110.00'")->fetchColumn());
    }
}
