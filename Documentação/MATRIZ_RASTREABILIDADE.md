# Matriz de Rastreabilidade - PDV UniGoiás

## Legenda

| Sigla | Significado |
|---|---|
| RF | Requisito funcional |
| RNF | Requisito nao funcional |
| CT | Caso de teste |
| TA | Teste automatizado |
| TM | Teste manual |

## Requisitos funcionais

| ID | Requisito | Funcionalidades/rotas | Componentes | Testes relacionados | Situacao |
|---|---|---|---|---|---|
| RF-001 | Permitir autenticacao de usuarios. | `/login`, `/login/logar`, `/login/logout` | `LoginController`, `Logged`, `Session` | CT-001, CT-002 | Implementado |
| RF-002 | Permitir recuperacao de senha. | `/login/senha`, `/login/senha/recuperacao/{token}` | `Login/SenhaController`, `RecuperacaoDeSenha`, `SendEmail` | CT-003 | Implementado |
| RF-003 | Controlar acesso por perfil de usuario; vendedor restrito a consulta de precos e catalogo. | Rotas protegidas por regras de perfil; redirecionamento do vendedor para `/catalogo` | `PerfilAcessoRule`, `ConfigPerfil`, `Usuario`, `index.php`, `menuLeft.php`, `default.php` | CT-004, CT-052 | Implementado |
| RF-004 | Permitir cadastro, edicao, ativacao e desativacao de usuarios. | `/usuario`, `/usuario/save`, `/usuario/update`, `/usuario/ativarUsuario`, `/usuario/desativarUsuario` | `UsuarioController`, `Usuario`, `BuscaUsuariosService` | CT-005, CT-006 | Implementado |
| RF-005 | Permitir cadastro, edicao, pesquisa e exclusao de produtos. | `/produto`, `/produto/save`, `/produto/update`, `/produto/pesquisarProdutoPorNome`, `/produto/pesquisarProdutoPorCodigoDeBarras`, `/produto/excluirProduto` | `ProdutoController`, `Produto`, `ProdutoCategoria` | CT-007, CT-008, CT-009 | Implementado |
| RF-006 | Permitir cadastro e manutencao de clientes. | `/cliente`, `/cliente/save`, `/cliente/update`, `/cliente/ativarCliente`, `/cliente/desativarCliente` | `ClienteController`, `Cliente` | CT-010, CT-011 | Implementado |
| RF-007 | Validar unicidade de email, CPF e CNPJ de cliente. | `/cliente/verificaSeEmailExiste`, `/cliente/verificaSeCpfExiste`, `/cliente/verificaSeCnpjExiste` | `ClienteController`, `Cliente` | CT-012 | Implementado |
| RF-008 | Permitir cadastro e consulta de enderecos do cliente. | `/clienteEndereco/save`, `/clienteEndereco/update`, `/clienteEndereco/buscarEnderecoViaCep` | `ClienteEnderecoController`, `ClienteEndereco` | CT-013, CT-014 | Implementado |
| RF-009 | Permitir operacao de venda no PDV padrao. | `/pdvPadrao`, `/pdvPadrao/save`, rotas de carrinho | `PdvPadraoController`, `CarrinhoDeCompras`, `Venda` | CT-015, CT-016, CT-017 | Implementado |
| RF-010 | Permitir operacao de venda no PDV diferencial. | `/pdvDiferencial`, `/pdvDiferencial/saveVendasViaSession`, pesquisas de produto | `PdvDiferencialController`, `VendasEmSessaoRepository`, JS do PDV | CT-018, CT-019, CT-020 | Implementado |
| RF-011 | Calcular total de produtos e troco da venda. | `/obterValorTotalDosProdutosNaMesa`, `/calcularTroco/{valorRecebido}` | Controllers de PDV, `CarrinhoDeCompras` | CT-017, CT-020 | Implementado |
| RF-012 | Emitir cupom fiscal mock ao finalizar venda. | Fluxo de finalizacao de venda | `CupomFiscalEmitter`, `MokitoImpostoMocker` | CT-021, TA-001 | Implementado |
| RF-013 | Emitir nota fiscal mock ao finalizar venda. | Fluxo de finalizacao de venda | `NotaFiscalEmitter`, `MokitoImpostoMocker` | CT-022, TA-002 | Implementado |
| RF-014 | Permitir abertura, fechamento e monitoramento de caixa. | `/caixa/abrir`, `/caixa/fechar`, `/caixa/ping` | `CaixaController`, `Caixa`, `CaixaSessao`, `CaixaAbertoRule` | CT-023, CT-024 | Implementado |
| RF-015 | Permitir registro de movimentacoes no fluxo de caixa. | `/fluxoDeCaixa/index`, `/fluxoDeCaixa/save`, `/fluxoDeCaixa/update` | `FluxoCaixaController`, `FluxoCaixa` | CT-025, CT-026 | Implementado |
| RF-016 | Permitir criacao e finalizacao de pedidos. | `/pedido`, `/pedido/adicionarProduto`, `/pedido/finalizarPedido` | `PedidoController`, `Pedido`, `ProdutoPedido` | CT-027, CT-028, CT-029 | Implementado |
| RF-017 | Permitir consulta de relatorios de vendas por periodo. | `/relatorio/vendasPorPeriodo`, `/relatorio/vendasChamadaAjax` | `RelatorioController`, `RelatorioVendasPorPeriodoRepository` | CT-030 | Implementado |
| RF-018 | Permitir exportacao de relatorios em PDF e XLS. | `/relatorio/gerarPDF`, `/relatorio/gerarXls`, `/relatorio/gerarPdfDeUmaVenda` | Services de PDF/XLS | CT-031, CT-032 | Implementado |
| RF-019 | Permitir cadastro e manutencao de empresas. | `/empresa`, `/empresa/save`, `/empresa/update` | `EmpresaController`, `Empresa` | CT-033, CT-034 | Implementado |
| RF-020 | Permitir configuracao do tipo de PDV. | `/configuracao`, `/configuracao/alterarConfigPdv` | `ConfiguracaoController`, `ConfigPdv`, `TipoPdv` | CT-035 | Implementado |
| RF-021 | Permitir consulta de logs de acesso. | `/logs` | `LogAcessoController`, `LogAcesso` | CT-036 | Implementado |
| RF-022 | Permitir registro externo/criacao de conta. | `/criarConta/index` | `CadastroExternoController` | CT-037 | Implementado |
| RF-023 | Permitir consulta de precos com ficha comercial (preco oficial, minimo e desconto maximo). | `/catalogo`, `/catalogo/buscar`, `/catalogo/ficha/{id}` | `CatalogoController`, `CatalogoProdutoRepository` | CT-045, TA-003 | Implementado |
| RF-024 | Simular negociacao por preco final, desconto em reais ou percentual, sinalizando preco abaixo do minimo. | `/catalogo/simular` | `CatalogoController`, `PrecoCalculator` | CT-046, TA-004 | Implementado |
| RF-025 | Montar simulacao de compra com varios produtos, subtotais e total geral, sem registrar venda. | `/catalogo` (lista "Produtos da compra") | `App/Views/catalogo/consulta.php` | CT-047 | Implementado |
| RF-026 | Gerenciar catalogo de produtos com historico de alteracoes e exclusao logica. | `/catalogo/gerenciar`, `/catalogo/formulario/{idProduto?}`, `/catalogo/salvar`, `/catalogo/excluir`, `/catalogo/historico/{idProduto?}` | `CatalogoController`, `CatalogoProdutoRepository`, `produtos_historico` | CT-048, CT-049, TA-003 | Implementado |
| RF-027 | Proteger o preco oficial: informado no cadastro por quem gerencia o catalogo, alterado depois so por gerente/administrador. | `/catalogo/salvar` | `CatalogoAcesso`, `CatalogoController` | CT-049, TA-003 | Implementado |
| RF-029 | Registrar valor de custo do produto e alertar quando o preco negociado estiver abaixo ou perto do custo (margem abaixo de 10%), sem expor o custo a vendedores sem permissao. | `/catalogo/salvar`, `/catalogo/simular`, `/catalogo/buscar` | `PrecoCalculator::avaliarCusto`, `CatalogoController`, `CatalogoProdutoRepository` | TA-004 | Implementado |
| RF-028 | Conceder e revogar a permissao de gerenciar catalogo a um unico usuario por empresa. | `/catalogo/permissao`, `/catalogo/conceder`, `/catalogo/revogar` | `CatalogoPermissao`, `CatalogoAcesso` | CT-050, CT-051, TA-003 | Implementado |

## Requisitos nao funcionais

| ID | Requisito | Evidencia no projeto | Testes relacionados | Situacao |
|---|---|---|---|---|
| RNF-001 | Executar em ambiente containerizado para desenvolvimento local. | `docker-compose.yml`, `.docker/` | CT-038 | Implementado |
| RNF-002 | Utilizar banco MySQL com inicializacao automatica (local). | `.docker/db/init/`, `Database/` | CT-039 | Implementado |
| RNF-003 | Gerenciar dependencias por Composer. | `composer.json`, `composer.lock` | CT-040 | Implementado |
| RNF-004 | Proteger formularios com CSRF. | `System/Session/Token.php` | CT-041 | Implementado |
| RNF-005 | Permitir execucao de testes automatizados. | `tests/Unit`, `tests/Feature`, `phpunit/phpunit` | TA-001 a TA-004 | Parcial |
| RNF-006 | Gerar relatorios em formatos portaveis. | Dompdf e PhpSpreadsheet | CT-031, CT-032 | Implementado |
| RNF-007 | Operar em producao em hospedagem compartilhada (HostGator/cPanel), com codigo e `.env` fora da pasta publica. | `public/index.php`, `public/.htaccess` (`RewriteBase /`), `.env.hostgator.example`, `IMPLANTACAO_HOSTGATOR.md` | CT-042, CT-043 | Implementado |
| RNF-008 | Servir o sistema por HTTPS atras do proxy da Cloudflare. | Deteccao de `X-Forwarded-Proto` em `index.php`; DNS `pdv` com proxy | CT-044 | Implementado |
| RNF-009 | Interface utilizavel em celular (Safari e Chrome), com menu responsivo rapido. | Script do menu e transicoes de 180 ms em `App/Views/Layouts/default.php` | CT-053 | Implementado |
| RNF-010 | Armazenar senhas com bcrypt, migrando hashes legados no login. | `System/Auth/Auth.php`, `System/Helpers/simpleHelpers.php` | CT-001, CT-054 | Implementado |

## Testes automatizados existentes

| ID | Arquivo | Objetivo | Requisitos cobertos |
|---|---|---|---|
| TA-001 | `tests/Unit/CupomFiscalEmitterTest.php` | Validar emissao de cupom fiscal mock com impostos simulados. | RF-012 |
| TA-002 | `tests/Unit/NotaFiscalEmitterTest.php` | Validar emissao de nota fiscal mock com impostos simulados. | RF-013 |
| TA-003 | `tests/Feature/CatalogoFeatureTest.php` | Validar cadastro, pesquisas, historico, exclusao logica, isolamento por empresa, codigos duplicados, permissoes por perfil, permissao unica por empresa e protecao do preco oficial (inclusive via HTTP). | RF-023, RF-026, RF-027, RF-028 |
| TA-004 | `tests/Unit/PrecoCalculatorTest.php` | Validar conversao para centavos, desconto maximo, simulacao por preco/desconto/percentual, regra do preco minimo, precisao decimal e entradas invalidas. | RF-024 |

## Lacunas e recomendacoes

| Area | Lacuna | Recomendacao |
|---|---|---|
| Autenticacao | Sem teste automatizado identificado para login/logout. | Criar testes de integracao para credenciais validas, invalidas e sessao expirada. |
| Perfis | Sem teste automatizado identificado para autorizacao por perfil. | Criar testes cobrindo acessos permitidos e negados por perfil. |
| Carrinho/PDV | Sem teste automatizado para adicionar/remover produtos e calcular troco. | Criar testes unitarios para carrinho e testes de integracao para finalizacao de venda. |
| Cadastros | Sem testes automatizados para validacoes de produto, cliente e usuario. | Criar testes para campos obrigatorios, duplicidade e soft delete. |
| Relatorios | Sem testes automatizados para filtros e exportacoes. | Criar testes para periodo valido, periodo sem vendas e geracao de arquivos. |
| Docker | Sem teste automatizado de subida do ambiente. | Validar em pipeline com `docker-compose up` e health checks. |
| Producao | Publicacao manual pelo cPanel, sem pipeline de deploy. | Manter o roteiro de `IMPLANTACAO_HOSTGATOR.md` e avaliar deploy automatizado (Git do cPanel ou SFTP). |
| Banco de producao | `Database/database_hostgator.sql` desatualizado (sem dados e sem tabelas do catalogo; colunas legadas em `usuarios`). | Gerar novo script a partir do esquema local atual antes de nova instalacao. |
| Simulador | Lista de varios produtos sem teste automatizado (comportamento em JavaScript). | Criar teste de interface (ex.: Playwright) para adicionar, remover e totalizar itens. |
| Menu mobile | Sem teste automatizado em navegadores moveis. | Validar manualmente (CT-053) a cada mudanca de layout. |
