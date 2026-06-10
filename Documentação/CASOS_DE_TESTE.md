# Casos de Teste - PDV UniGoiás

## 1. Objetivo

Este documento descreve cenarios de validacao para as principais funcionalidades do PDV UniGoiás, incluindo testes manuais recomendados e testes automatizados existentes. Os casos foram organizados para apoiar validacao funcional, regressao e rastreabilidade.

## 2. Ambiente de teste

| Item | Valor |
|---|---|
| Aplicacao | PDV MASTER |
| URL padrao | `http://localhost:8080` |
| Banco | MySQL via Docker |
| Usuario inicial | `admin@pdvmaster.com.br` |
| Senha inicial | `admin123` |
| Navegador sugerido | Chrome, Edge ou Firefox |

## 3. Dados basicos

| Tipo | Dados sugeridos |
|---|---|
| Produto | Cafe 500g, codigo `789100000001`, preco `20.00`, estoque `50` |
| Cliente PF | Maria Teste, CPF valido para ambiente de teste, email `maria.teste@example.com` |
| Cliente PJ | Empresa Teste LTDA, CNPJ valido para ambiente de teste, email `empresa.teste@example.com` |
| Venda | 2 unidades do produto Cafe 500g, pagamento em dinheiro |

## 4. Casos de teste funcionais

| ID | Cenario | Pre-condicoes | Passos | Resultado esperado | Tipo | Requisito |
|---|---|---|---|---|---|---|
| CT-001 | Login com credenciais validas | Usuario ativo cadastrado | Acessar `/login`, informar email e senha validos, confirmar login | Sistema autentica e redireciona para home/dashboard | Manual | RF-001 |
| CT-002 | Login com credenciais invalidas | Usuario existente | Acessar `/login`, informar senha incorreta, confirmar login | Sistema nega acesso e exibe mensagem de erro | Manual | RF-001 |
| CT-003 | Recuperacao de senha | Usuario com email cadastrado | Acessar `/login/senha`, informar email, abrir link de recuperacao e cadastrar nova senha | Sistema permite redefinir a senha e login posterior com a nova senha | Manual | RF-002 |
| CT-004 | Bloqueio por perfil sem permissao | Usuario com perfil restrito | Autenticar com perfil vendedor e acessar tela administrativa | Sistema bloqueia acesso ou redireciona conforme regra | Manual | RF-003 |
| CT-005 | Cadastro de usuario | Usuario administrador autenticado | Acessar usuarios, abrir formulario, preencher dados obrigatorios e salvar | Usuario aparece na listagem e pode autenticar conforme perfil | Manual | RF-004 |
| CT-006 | Desativacao e ativacao de usuario | Usuario cadastrado | Desativar usuario, tentar login, reativar usuario e tentar login novamente | Login bloqueado quando inativo e permitido apos reativacao | Manual | RF-004 |
| CT-007 | Cadastro de produto | Usuario com permissao autenticado | Acessar produtos, preencher nome, categoria, preco, estoque e codigo, salvar | Produto e exibido na listagem e fica disponivel para venda | Manual | RF-005 |
| CT-008 | Pesquisa de produto por nome | Produto cadastrado | Pesquisar parte do nome do produto | Sistema retorna produtos correspondentes | Manual | RF-005 |
| CT-009 | Pesquisa de produto por codigo de barras | Produto com codigo cadastrado | Pesquisar pelo codigo de barras | Sistema retorna o produto correto | Manual | RF-005 |
| CT-010 | Cadastro de cliente PF | Usuario autenticado | Acessar clientes, preencher dados de pessoa fisica e salvar | Cliente e cadastrado e listado como ativo | Manual | RF-006 |
| CT-011 | Desativacao e ativacao de cliente | Cliente cadastrado | Desativar cliente e depois ativar novamente | Status do cliente e alterado corretamente | Manual | RF-006 |
| CT-012 | Validacao de CPF/CNPJ/email duplicado | Cliente ja cadastrado | Tentar cadastrar outro cliente com os mesmos documentos/email | Sistema informa duplicidade e impede cadastro indevido | Manual | RF-007 |
| CT-013 | Cadastro de endereco de cliente | Cliente cadastrado | Abrir enderecos, preencher CEP, logradouro, numero e salvar | Endereco fica vinculado ao cliente | Manual | RF-008 |
| CT-014 | Busca de endereco por CEP | Cliente cadastrado e CEP valido | Informar CEP no formulario de endereco | Sistema preenche os dados retornados ou informa falha de consulta | Manual | RF-008 |
| CT-015 | Adicionar produto no PDV padrao | Produto ativo cadastrado e caixa aberto | Acessar PDV padrao e adicionar produto | Produto aparece no carrinho/mesa | Manual | RF-009 |
| CT-016 | Alterar quantidade no PDV padrao | Produto no carrinho | Alterar quantidade para valor maior que 1 | Sistema recalcula subtotal e total | Manual | RF-009 |
| CT-017 | Calcular total e troco no PDV padrao | Produto no carrinho | Informar valor recebido maior que total | Sistema exibe troco correto | Manual | RF-011 |
| CT-018 | Pesquisar produto no PDV diferencial | Produto ativo cadastrado | Acessar PDV diferencial e pesquisar por nome ou codigo | Sistema encontra produto e permite adiciona-lo | Manual | RF-010 |
| CT-019 | Finalizar venda no PDV diferencial | Produto no carrinho e caixa aberto | Confirmar venda com forma de pagamento | Sistema registra venda e limpa carrinho/sessao | Manual | RF-010 |
| CT-020 | Calcular total e troco no PDV diferencial | Produto no carrinho | Informar valor recebido e acionar calculo de troco | Troco exibido corresponde a valor recebido menos total | Manual | RF-011 |
| CT-021 | Emitir cupom fiscal mock | Venda finalizada | Finalizar venda e verificar cupom fiscal | Cupom contem numero, chave, totais e impostos simulados | Automatizado/Manual | RF-012 |
| CT-022 | Emitir nota fiscal mock | Venda finalizada | Finalizar venda e verificar modal/retorno de nota fiscal | Nota contem modelo NFC-e, numero, chave de 44 digitos e totais | Automatizado/Manual | RF-013 |
| CT-023 | Abrir caixa | Usuario autorizado autenticado | Acionar abertura de caixa com valor inicial | Caixa fica aberto para operacoes de venda | Manual | RF-014 |
| CT-024 | Fechar caixa | Caixa aberto | Acionar fechamento informando valores finais | Caixa e encerrado e novas vendas exigem nova abertura | Manual | RF-014 |
| CT-025 | Registrar entrada no fluxo de caixa | Usuario autorizado autenticado | Acessar fluxo de caixa, registrar entrada e salvar | Movimentacao aparece com tipo e valor corretos | Manual | RF-015 |
| CT-026 | Registrar saida no fluxo de caixa | Usuario autorizado autenticado | Registrar saida/despesa e salvar | Movimentacao reduz saldo conforme regra exibida | Manual | RF-015 |
| CT-027 | Criar pedido com cliente e endereco | Cliente e endereco cadastrados | Acessar pedidos, selecionar cliente/endereco e salvar | Pedido e criado com dados vinculados | Manual | RF-016 |
| CT-028 | Adicionar produto ao pedido | Pedido criado e produto ativo | Adicionar produto e quantidade ao pedido | Item aparece no pedido e total e atualizado | Manual | RF-016 |
| CT-029 | Finalizar pedido | Pedido com itens | Acionar finalizacao do pedido | Pedido muda para situacao finalizada conforme regra | Manual | RF-016 |
| CT-030 | Consultar vendas por periodo | Vendas registradas | Acessar relatorio, informar periodo com vendas e pesquisar | Sistema lista vendas do periodo | Manual | RF-017 |
| CT-031 | Gerar relatorio PDF | Vendas no periodo | Acionar exportacao PDF | Arquivo PDF e gerado/baixado com dados do periodo | Manual | RF-018 |
| CT-032 | Gerar relatorio XLS | Vendas no periodo | Acionar exportacao XLS | Arquivo XLS e gerado/baixado com dados do periodo | Manual | RF-018 |
| CT-033 | Cadastro de empresa | Usuario administrador autenticado | Acessar empresas, preencher dados e salvar | Empresa aparece na listagem | Manual | RF-019 |
| CT-034 | Edicao de empresa | Empresa cadastrada | Alterar dados da empresa e salvar | Dados atualizados sao exibidos na listagem | Manual | RF-019 |
| CT-035 | Alterar tipo de PDV | Usuario autorizado autenticado | Acessar configuracoes, alterar tipo de PDV e salvar | Sistema passa a usar configuracao selecionada | Manual | RF-020 |
| CT-036 | Consultar logs de acesso | Usuario com permissao autenticado | Acessar `/logs` | Sistema lista registros de acesso disponiveis | Manual | RF-021 |
| CT-037 | Abrir tela de criacao de conta externa | Ambiente disponivel | Acessar `/criarConta/index` | Tela de registro externo e exibida sem erro | Manual | RF-022 |

## 5. Casos de teste nao funcionais

| ID | Cenario | Passos | Resultado esperado | Tipo | Requisito |
|---|---|---|---|---|---|
| CT-038 | Subida do ambiente Docker | Executar `docker-compose up -d --build` | Containers da aplicacao e banco sobem sem erro | Manual | RNF-001 |
| CT-039 | Inicializacao do banco | Subir ambiente limpo com volume novo | Tabelas e dados iniciais sao criados | Manual | RNF-002 |
| CT-040 | Instalacao de dependencias | Executar `composer install` | Dependencias sao instaladas e autoload e gerado | Manual | RNF-003 |
| CT-041 | Validacao de CSRF em formulario | Remover/alterar token de formulario protegido e enviar requisicao | Sistema rejeita ou nao processa requisicao invalida | Manual | RNF-004 |

## 6. Testes automatizados existentes

### TA-001 - Emissao de cupom fiscal mock

| Campo | Descricao |
|---|---|
| Arquivo | `tests/Unit/CupomFiscalEmitterTest.php` |
| Objetivo | Validar que o emissor de cupom utiliza o simulador de impostos e retorna status, codigo, totais, numero e chave fiscal. |
| Requisito | RF-012 |
| Comando | `vendor/bin/phpunit tests/Unit/CupomFiscalEmitterTest.php` |

### TA-002 - Emissao de nota fiscal mock

| Campo | Descricao |
|---|---|
| Arquivo | `tests/Unit/NotaFiscalEmitterTest.php` |
| Objetivo | Validar que o emissor de nota fiscal utiliza o simulador de impostos e retorna status, modelo, codigo da venda, totais e chave de acesso com 44 digitos. |
| Requisito | RF-013 |
| Comando | `vendor/bin/phpunit tests/Unit/NotaFiscalEmitterTest.php` |

## 7. Criterios de aceite

- Todos os casos criticos de login, PDV, caixa, venda e fiscal mock devem ser aprovados antes da entrega.
- Casos de cadastro devem validar criacao, edicao e bloqueio de duplicidades.
- Relatorios devem ser testados com periodo com dados e periodo sem dados.
- Testes automatizados existentes devem executar sem falhas.
- Falhas encontradas devem ser registradas com evidencia, passos de reproducao e resultado obtido.
