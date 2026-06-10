# Documentacao Tecnica - PDV UniGoiás

## 1. Visao geral

O PDV UniGoiás e um sistema de ponto de venda desenvolvido em PHP, com arquitetura MVC, banco de dados MySQL e ambiente de execucao via Docker. O sistema atende operacoes de venda, cadastro de produtos e clientes, controle de usuarios, abertura e fechamento de caixa, pedidos, relatorios e emissao fiscal simulada para homologacao.

## 2. Tecnologias

| Camada | Tecnologia |
|---|---|
| Linguagem | PHP 8.x |
| Banco de dados | MySQL 8 |
| Servidor web | Apache via Docker |
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
├── docker-compose.yml   # Orquestracao dos containers
├── composer.json        # Dependencias PHP e autoload
└── Documentação/        # Documentacao tecnica do projeto
```

## 4. Arquitetura

O sistema utiliza um fluxo MVC simples:

1. A requisicao chega ao `index.php`.
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
| 4 | Vendedor | PDV |
| 5 | Gerente | PDV e relatorios |

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
| Clientes | `/cliente`, `/cliente/save`, `/cliente/update` |
| Pedidos | `/pedido`, `/pedido/finalizarPedido` |
| Relatorios | `/relatorio`, `/relatorio/vendasPorPeriodo`, `/relatorio/gerarPDF`, `/relatorio/gerarXls` |
| Empresa | `/empresa`, `/empresa/save`, `/empresa/update` |
| Fluxo de caixa | `/fluxoDeCaixa/index`, `/fluxoDeCaixa/save` |
| Logs | `/logs` |

## 7. Banco de dados

O projeto possui arquivos SQL em `Database/` e scripts de inicializacao Docker em `.docker/db/init/`. O banco e criado automaticamente quando o ambiente Docker e iniciado pela primeira vez.

Arquivos relevantes:

- `Database/database.sql`
- `Database/seed.sql`
- `.docker/db/init/01_schema.sql`
- `.docker/db/init/02_seed.sql`

## 8. Ambiente de execucao

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

Credencial inicial informada no README:

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

Pontos de atencao:

- Substituir hash legado SHA1 por `password_hash`/`password_verify` em evolucao futura.
- Revisar exposicao de arquivos de debug antes de producao.
- Garantir que `.env` real nao seja versionado em repositorios publicos.
- Evoluir fiscal mock para integracao homologada quando houver necessidade fiscal real.

## 10. Testes

Testes automatizados existentes:

- Emissao de nota fiscal mock com impostos simulados.
- Emissao de cupom fiscal mock com impostos simulados.

Comando recomendado:

```bash
vendor/bin/phpunit tests
```

Como nao foi identificado arquivo `phpunit.xml`, a execucao pode ser feita apontando diretamente para a pasta `tests`.

## 11. Manutencao recomendada

- Manter novas rotas documentadas em `routes/routes.php` e refletidas nesta documentacao.
- Atualizar a matriz de rastreabilidade sempre que houver novo requisito.
- Criar testes automatizados para regras fiscais, carrinho, calculo de troco, permissoes e relatorios.
- Evitar duplicacao entre pastas legadas e services atuais.
- Revisar arquivos temporarios ou de debug antes de releases.
