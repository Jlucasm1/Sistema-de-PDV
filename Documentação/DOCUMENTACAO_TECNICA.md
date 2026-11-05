# Documentacao Tecnica - PDV UniGoiás

## 1. Visao geral

O PDV UniGoiás e um sistema de ponto de venda desenvolvido em PHP, com arquitetura MVC e banco de dados MySQL. O sistema atende operacoes de venda, cadastro de produtos e clientes, consulta de precos com simulador de negociacao, catalogo de produtos, controle de usuarios, abertura e fechamento de caixa, pedidos, relatorios e emissao fiscal simulada para homologacao.

O sistema esta em producao em `https://pdv.marianoprime.com`, hospedado na HostGator (hospedagem compartilhada com cPanel) e servido pela Cloudflare (DNS, proxy e HTTPS). O ambiente Docker e usado para desenvolvimento local. Detalhes de producao em `IMPLANTACAO_HOSTGATOR.md`.

## 2. Tecnologias

| Camada | Tecnologia |
|---|---|
| Linguagem | PHP 8.x |
| Banco de dados | MySQL 8 (local, Docker) e MySQL 5.7 (producao, HostGator) |
| Servidor web | Apache via Docker (local) e Apache da HostGator (producao) |
| DNS, proxy e HTTPS | Cloudflare |
| Dependencias | Composer |
| Testes | PHPUnit 10 |
| Frontend | PHP Views, Bootstrap, jQuery e assets locais |
| Relatorios | Dompdf e PhpSpreadsheet |
| Email | PHPMailer |

## 3. Estrutura do projeto

```text
pdv-final/
├── App/
│   ├── Config/          # Configuracoes de perfis e constantes de negocio
│   ├── Controllers/     # Controllers da aplicacao MVC
│   ├── Models/          # Models vinculados ao banco de dados
│   ├── Repositories/    # Consultas e operacoes especializadas
│   ├── Rules/           # Regras de autenticacao, acesso e caixa
│   ├── Services/        # Servicos de dominio, relatorios, fiscal, upload e email
│   └── Views/           # Telas renderizadas pela aplicacao
├── Database/            # Dumps e seeds do banco
├── System/              # Framework interno, roteamento, sessao, helpers e banco
├── public/              # Assets publicos e front controller publico
├── routes/routes.php    # Mapeamento das rotas
├── tests/               # Testes automatizados
├── docker-compose.yml   # Orquestracao dos containers (desenvolvimento)
├── .env.hostgator.example # Modelo de configuracao de producao
├── composer.json        # Dependencias PHP e autoload
└── Documentação/        # Documentacao tecnica do projeto
```

Em producao, a raiz do documento do subdominio e a pasta `public/`. O restante do projeto, incluindo o `.env`, fica fora do acesso web.

## 4. Arquitetura

O sistema utiliza um fluxo MVC simples:

1. A requisicao chega a `public/index.php` (reescrita pelo `public/.htaccess`), que carrega o `index.php` da raiz.
2. O roteador definido em `routes/routes.php` identifica a rota.
3. O controller executa regras de negocio, consulta models/repositories e prepara dados.
4. As views em `App/Views` renderizam a interface.
5. A persistencia e realizada por PDO e classes internas em `System/Database`, `System/Model` e `System/NativeQuery`.

## 5. Modulos funcionais

### 5.1 Autenticacao e usuarios

Responsavel por login, logout, recuperacao de senha, cadastro e ativacao/desativacao de usuarios. O controle de acesso utiliza perfis definidos no projeto:

| ID | Perfil | Acesso previsto |
|---|---|---|
| 1 | Super Admin | Acesso total a todas as empresas |
| 2 | Administrador | Gestao completa da empresa |
| 3 | Operador | PDV, produtos e clientes |
| 4 | Vendedor | Somente Consulta de Precos e Catalogo (este ultimo apenas com permissao) |
| 5 | Gerente | PDV, relatorios e alteracao de preco oficial no catalogo |

Restricao do vendedor: o `index.php` redireciona o vendedor para `/catalogo` em qualquer rota fora de `catalogo` e `login` (requisicoes AJAX recebem HTTP 403). O menu lateral (`menuLeft.php`) e a barra superior (`default.php`) exibem ao vendedor apenas Consulta de Precos, Catalogo (se autorizado) e Sair.

Senhas sao armazenadas com `password_hash` (bcrypt). Hashes legados SHA1 ainda sao aceitos e convertidos automaticamente para bcrypt no primeiro login (`System/Auth/Auth.php`, `System/Helpers/simpleHelpers.php`).

Arquivos principais:

- `App/Controllers/LoginController.php`
- `App/Controllers/Login/SenhaController.php`
- `App/Controllers/UsuarioController.php`
- `App/Rules/Logged.php`
- `App/Rules/PerfilAcessoRule.php`

### 5.2 PDV padrao e diferencial

O sistema possui dois fluxos de venda:

- PDV Padrao: venda rapida com manipulacao de produtos em mesa/carrinho.
- PDV Diferencial: fluxo avancado com pesquisa por nome, codigo de barras e venda via sessao.

Funcionalidades principais:

- Adicionar produto ao carrinho.
- Alterar quantidade.
- Remover produto.
- Calcular total da venda.
- Calcular troco.
- Finalizar venda.
- Emitir cupom/nota fiscal mock.

Arquivos principais:

- `App/Controllers/PdvPadraoController.php`
- `App/Controllers/PdvDiferencialController.php`
- `App/Services/CarrinhoDeCompras.php`
- `public/js/venda/funcoesPdvAvancado.js`

### 5.3 Fiscal mock

A implementacao fiscal atual e simulada e tem objetivo de homologacao/desenvolvimento. Ela nao substitui integracao oficial com SEFAZ, NFC-e ou SAT.

Servicos:

- `App/Services/Fiscal/MokitoImpostoMocker.php`: simula ICMS, PIS e COFINS.
- `App/Services/Fiscal/NotaFiscalEmitter.php`: monta nota fiscal mock.
- `App/Services/Fiscal/CupomFiscalEmitter.php`: monta cupom fiscal mock.
- `App/Services/Fiscal/ImpostoMockerInterface.php`: contrato para simuladores de impostos.

Testes automatizados:

- `tests/Unit/NotaFiscalEmitterTest.php`
- `tests/Unit/CupomFiscalEmitterTest.php`

### 5.4 Produtos

Permite cadastrar, editar, pesquisar e excluir produtos. O modulo tambem contempla categorias e codigo de barras.

Arquivos principais:

- `App/Controllers/ProdutoController.php`
- `App/Controllers/CategoriaProdutoController.php`
- `App/Models/Produto.php`
- `App/Models/ProdutoCategoria.php`
- `App/Views/produto/`

### 5.4.1 Consulta de precos, simulador e catalogo

Modulo voltado ao vendedor para consultar precos e simular negociacoes sem registrar venda.

Consulta de precos e simulador (`/catalogo`):

- Pesquisa por nome, descricao, codigo interno ou codigo de barras.
- Ficha comercial com preco oficial, preco minimo autorizado e desconto maximo.
- Simulacao por preco final, desconto em reais ou percentual, com indicacao quando o preco fica abaixo do minimo (exige autorizacao de gerente/administrador).
- Lista "Produtos da compra": varios produtos simulados com quantidade, subtotal, situacao do preco e total geral. A lista e temporaria e nao registra venda.

Gerenciamento do catalogo (`/catalogo/gerenciar`):

- Cadastro, edicao e exclusao logica de produtos, com historico de alteracoes (`produtos_historico`).
- Preco oficial: pode ser informado no cadastro por quem gerencia o catalogo; depois do cadastro, so gerente, administrador ou super admin altera.
- Preco minimo nao pode ser maior que o preco oficial.
- Valor de custo (coluna `preco_compra`): opcional, usado como parametro de margem. O simulador alerta quando o preco negociado fica abaixo do custo (prejuizo) ou com margem abaixo de 10% sobre o custo (`PrecoCalculator::MARGEM_ALERTA_CUSTO`). O valor do custo, a margem e o lucro so sao exibidos a quem gerencia o catalogo; os demais vendedores veem apenas o alerta.
- Codigos duplicados na mesma empresa sao rejeitados; dados isolados por empresa.

Permissao `gerenciar_catalogo_produtos`:

- Perfis administrativos (super admin, administrador, gerente) gerenciam o catalogo por padrao.
- Um vendedor so gerencia o catalogo se receber a permissao, concedida por super admin ou administrador em `/catalogo/permissao`.
- Apenas um usuario por empresa pode ter a permissao; ao conceder a outro, o anterior perde o acesso.
- A permissao so vale para usuario ativo (`status = 1` e `deleted_at` nulo).

Calculo de precos em centavos inteiros para evitar erro de ponto flutuante (`PrecoCalculator`).

Arquivos principais:

- `App/Controllers/CatalogoController.php`
- `App/Rules/CatalogoAcesso.php`
- `App/Models/CatalogoPermissao.php`
- `App/Repositories/CatalogoProdutoRepository.php`
- `App/Services/Catalogo/PrecoCalculator.php`
- `App/Views/catalogo/` (`consulta.php`, `gerenciar.php`, `formulario.php`, `permissao.php`)

### 5.5 Clientes e enderecos

Permite cadastro, atualizacao, ativacao/desativacao, validacao de CPF/CNPJ/email e gerenciamento de enderecos.

Arquivos principais:

- `App/Controllers/ClienteController.php`
- `App/Controllers/ClienteEnderecoController.php`
- `App/Models/Cliente.php`
- `App/Models/ClienteEndereco.php`
- `App/Views/cliente/`
- `App/Views/clienteEndereco/`

### 5.6 Pedidos

Modulo de pedidos com cliente, endereco, produtos, alteracao de quantidade, finalizacao e mudanca de situacao.

Arquivos principais:

- `App/Controllers/PedidoController.php`
- `App/Models/Pedido.php`
- `App/Models/ProdutoPedido.php`
- `App/Views/pedido/`

### 5.7 Caixa e fluxo de caixa

Controla abertura/fechamento de caixa, ping de sessao de caixa e lancamentos financeiros.

Arquivos principais:

- `App/Controllers/CaixaController.php`
- `App/Controllers/FluxoCaixa/FluxoCaixaController.php`
- `App/Models/Caixa.php`
- `App/Models/CaixaSessao.php`
- `App/Models/FluxoCaixa.php`
- `App/Rules/CaixaAbertoRule.php`

### 5.8 Relatorios

Gera relatorios de venda por periodo, detalhamento de itens, PDF de venda especifica, PDF consolidado e XLS.

Arquivos principais:

- `App/Controllers/RelatorioController.php`
- `App/Repositories/RelatorioVendasPorPeriodoRepository.php`
- `App/Services/RelatorioPDFService/`
- `App/Services/RelatorioXlsService/`
- `App/Views/relatorio/`

### 5.9 Configuracao, empresa e logs

Inclui configuracao do tipo de PDV, cadastro de empresas e consulta de logs de acesso.

Arquivos principais:

- `App/Controllers/ConfiguracaoController.php`
- `App/Controllers/EmpresaController.php`
- `App/Controllers/LogAcessoController.php`
- `App/Models/ConfigPdv.php`
- `App/Models/Empresa.php`
- `App/Models/LogAcesso.php`

## 6. Rotas principais

| Modulo | Rotas base |
|---|---|
| Login | `/`, `/login`, `/login/logar`, `/login/logout`, `/login/senha` |
| Home | `/home` |
| Caixa | `/caixa/abrir`, `/caixa/fechar`, `/caixa/ping` |
| Usuarios | `/usuario`, `/usuario/save`, `/usuario/update` |
| PDV Padrao | `/pdvPadrao`, `/pdvPadrao/save` |
| PDV Diferencial | `/pdvDiferencial`, `/pdvDiferencial/saveVendasViaSession` |
| Produtos | `/produto`, `/produto/save`, `/produto/update` |
| Consulta de precos | `/catalogo`, `/catalogo/buscar`, `/catalogo/ficha/{id}`, `/catalogo/simular` |
| Catalogo | `/catalogo/gerenciar`, `/catalogo/formulario/{idProduto?}`, `/catalogo/salvar`, `/catalogo/excluir`, `/catalogo/historico/{idProduto?}` |
| Permissao do catalogo | `/catalogo/permissao`, `/catalogo/conceder`, `/catalogo/revogar` |
| Clientes | `/cliente`, `/cliente/save`, `/cliente/update` |
| Pedidos | `/pedido`, `/pedido/finalizarPedido` |
| Relatorios | `/relatorio`, `/relatorio/vendasPorPeriodo`, `/relatorio/gerarPDF`, `/relatorio/gerarXls` |
| Empresa | `/empresa`, `/empresa/save`, `/empresa/update` |
| Fluxo de caixa | `/fluxoDeCaixa/index`, `/fluxoDeCaixa/save` |
| Logs | `/logs` |

## 7. Banco de dados

O projeto possui arquivos SQL em `Database/` e scripts de inicializacao Docker em `.docker/db/init/`. No ambiente local, o banco e criado automaticamente quando o Docker e iniciado pela primeira vez.

Arquivos relevantes:

- `Database/database.sql`
- `Database/seed.sql`
- `Database/database_hostgator.sql` (estrutura para hospedagem compartilhada; nao inclui dados nem tabelas do catalogo)
- `.docker/db/init/01_schema.sql` (esquema local atual)
- `.docker/db/init/02_seed.sql`
- `.docker/db/migrations/001_preco_minimo.sql` e `002_catalogo_produtos.sql`

Tabelas do catalogo: `catalogo_permissoes`, `produtos_historico` e as colunas `codigo_interno`, `preco_compra` e `preco_minimo` em `produtos`.

O banco de producao (HostGator, MySQL 5.7) foi criado a partir de um esquema legado e recebeu scripts complementares. As diferencas conhecidas e as correcoes aplicadas estao em `IMPLANTACAO_HOSTGATOR.md`, secao 4.

## 8. Ambientes de execucao

### 8.1 Producao

| Item | Valor |
|---|---|
| URL | `https://pdv.marianoprime.com` |
| Hospedagem | HostGator (cPanel), projeto em `public_html/pdv`, raiz do documento em `public_html/pdv/public` |
| DNS/HTTPS | Cloudflare com proxy ativo |
| Configuracao | `.env` baseado em `.env.hostgator.example` (`APP_ENV=production`, `APP_DISPLAY_ERRORS=false`) |

Procedimentos de publicacao e diagnostico: `IMPLANTACAO_HOSTGATOR.md`.

### 8.2 Desenvolvimento local (Docker)

Passos principais:

```bash
cp .env.example .env
docker-compose up -d --build
```

Servicos padrao:

| Servico | URL |
|---|---|
| Aplicacao | `http://localhost:8080` |
| PHPMyAdmin | `http://localhost:8081` |

Credencial inicial do ambiente local informada no README (nao vale para producao):

| Campo | Valor |
|---|---|
| Email | `admin@pdvmaster.com.br` |
| Senha | `admin123` |

## 9. Seguranca

Controles identificados no projeto:

- Autenticacao por sessao.
- Controle de acesso por perfil.
- CSRF token via `System/Session/Token.php`.
- Sessao com configuracoes de seguranca.
- Conexao PDO com tratamento de excecoes.
- Soft delete/ativacao e desativacao em entidades principais.
- Recuperacao de senha por fluxo especifico.
- Senhas com bcrypt (`password_hash`), com migracao automatica de hashes SHA1 legados no login.
- Regras de acesso do catalogo sempre verificadas no backend (`CatalogoAcesso`).
- Em producao: HTTPS pela Cloudflare, exibicao de erros PHP desativada e `.env` fora da pasta publica.

Pontos de atencao:

- Arquivos de debug na raiz (`debug_login.php`, `teste_env.php`) nao devem ser enviados para producao.
- Garantir que `.env` real nao seja versionado em repositorios publicos.
- Evoluir fiscal mock para integracao homologada quando houver necessidade fiscal real.
- Remover o salt fixo legado de `legacyPasswordHashes` quando nao houver mais senhas SHA1 no banco.

## 10. Testes

Testes automatizados existentes:

- Emissao de nota fiscal mock com impostos simulados.
- Emissao de cupom fiscal mock com impostos simulados.
- Calculo de precos, descontos e preco minimo (`tests/Unit/PrecoCalculatorTest.php`).
- Cadastro, permissoes, historico, isolamento por empresa e protecao do preco oficial no catalogo (`tests/Feature/CatalogoFeatureTest.php`).

Comando recomendado (no container local, pois o PHP nao precisa estar instalado no Windows):

```bash
docker exec pdv_master_app sh -lc 'cd /var/www/html && php vendor/bin/phpunit tests'
```

Como nao foi identificado arquivo `phpunit.xml`, a execucao pode ser feita apontando diretamente para a pasta `tests`. Os testes de catalogo precisam do banco local ativo.

## 11. Manutencao recomendada

- Manter novas rotas documentadas em `routes/routes.php` e refletidas nesta documentacao.
- Atualizar a matriz de rastreabilidade sempre que houver novo requisito.
- Criar testes automatizados para regras fiscais, carrinho, calculo de troco, permissoes e relatorios.
- Evitar duplicacao entre pastas legadas e services atuais.
- Revisar arquivos temporarios ou de debug antes de releases.
- Atualizar `Database/database_hostgator.sql` com o esquema atual antes de uma nova instalacao em producao.
- Ao alterar CSS/JS, atualizar `$assetVersion` nos layouts para evitar cache do navegador e da Cloudflare.
- Seguir o procedimento de publicacao em `IMPLANTACAO_HOSTGATOR.md`, sempre com backup de arquivos e banco.
