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
| RF-003 | Controlar acesso por perfil de usuario. | Rotas protegidas por regras de perfil | `PerfilAcessoRule`, `ConfigPerfil`, `Usuario` | CT-004 | Implementado |
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

## Requisitos nao funcionais

| ID | Requisito | Evidencia no projeto | Testes relacionados | Situacao |
|---|---|---|---|---|
| RNF-001 | Executar em ambiente containerizado. | `docker-compose.yml`, `.docker/` | CT-038 | Implementado |
| RNF-002 | Utilizar banco MySQL com inicializacao automatica. | `.docker/db/init/`, `Database/` | CT-039 | Implementado |
| RNF-003 | Gerenciar dependencias por Composer. | `composer.json`, `composer.lock` | CT-040 | Implementado |
| RNF-004 | Proteger formularios com CSRF. | `System/Session/Token.php` | CT-041 | Implementado |
| RNF-005 | Permitir execucao de testes automatizados. | `tests/Unit`, `phpunit/phpunit` | TA-001, TA-002 | Parcial |
| RNF-006 | Gerar relatorios em formatos portaveis. | Dompdf e PhpSpreadsheet | CT-031, CT-032 | Implementado |

## Testes automatizados existentes

| ID | Arquivo | Objetivo | Requisitos cobertos |
|---|---|---|---|
| TA-001 | `tests/Unit/CupomFiscalEmitterTest.php` | Validar emissao de cupom fiscal mock com impostos simulados. | RF-012 |
| TA-002 | `tests/Unit/NotaFiscalEmitterTest.php` | Validar emissao de nota fiscal mock com impostos simulados. | RF-013 |

## Lacunas e recomendacoes

| Area | Lacuna | Recomendacao |
|---|---|---|
| Autenticacao | Sem teste automatizado identificado para login/logout. | Criar testes de integracao para credenciais validas, invalidas e sessao expirada. |
| Perfis | Sem teste automatizado identificado para autorizacao por perfil. | Criar testes cobrindo acessos permitidos e negados por perfil. |
| Carrinho/PDV | Sem teste automatizado para adicionar/remover produtos e calcular troco. | Criar testes unitarios para carrinho e testes de integracao para finalizacao de venda. |
| Cadastros | Sem testes automatizados para validacoes de produto, cliente e usuario. | Criar testes para campos obrigatorios, duplicidade e soft delete. |
| Relatorios | Sem testes automatizados para filtros e exportacoes. | Criar testes para periodo valido, periodo sem vendas e geracao de arquivos. |
| Docker | Sem teste automatizado de subida do ambiente. | Validar em pipeline com `docker-compose up` e health checks. |
