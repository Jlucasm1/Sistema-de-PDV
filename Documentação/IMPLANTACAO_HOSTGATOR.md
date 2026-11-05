# Implantacao em Producao - HostGator + Cloudflare

Este documento descreve como o PDV esta publicado em producao e como atualizar o sistema com seguranca.

## 1. Visao geral

| Item | Valor |
|---|---|
| Endereco publico | `https://pdv.marianoprime.com` |
| Hospedagem (origem) | HostGator, hospedagem compartilhada com cPanel |
| DNS, proxy e HTTPS de borda | Cloudflare |
| Dominio principal da conta | `marianoprime.com` (outro site, **nao faz parte do PDV**) |
| PHP | 8.x (definido em cPanel > Selecionar versao do PHP) |
| Banco de dados | MySQL 5.7 da HostGator, administrado pelo phpMyAdmin |

Fluxo de uma requisicao:

```text
Navegador -> Cloudflare (DNS + proxy + HTTPS) -> HostGator (Apache + PHP) -> MySQL
```

O ambiente Docker (`docker-compose.yml`) continua sendo usado apenas para desenvolvimento local.

## 2. Estrutura no servidor

O subdominio `pdv.marianoprime.com` foi criado em cPanel > Dominios com raiz propria, separada do site principal.

```text
public_html/
├── (arquivos do site marianoprime.com - nao alterar)
└── pdv/                      # Projeto completo do PDV
    ├── .env                  # Configuracao de producao (fora da pasta publica)
    ├── App/
    ├── System/
    ├── routes/
    ├── vendor/
    ├── index.php             # Front controller principal
    └── public/               # RAIZ DO DOCUMENTO do subdominio
        ├── index.php         # Carrega ../index.php
        ├── .htaccess         # Reescrita de URLs
        └── assets/, css/, js/, img/
```

Somente a pasta `public/` fica acessivel pela web. O `.env`, o codigo e o `vendor/` ficam fora dela.

## 3. Configuracoes obrigatorias

### 3.1 `.env` de producao

Criado a partir de `.env.hostgator.example`, na pasta `public_html/pdv/`:

| Variavel | Valor em producao |
|---|---|
| `APP_ENV` | `production` |
| `APP_DISPLAY_ERRORS` | `false` |
| `HOST_NAME` | `localhost` |
| `HOST_DBNAME`, `HOST_USERNAME`, `HOST_PASSWORD` | Banco e usuario criados no cPanel |
| `HASH` | Chave aleatoria unica |
| `SHARED_HOST` | `true` |

Nunca versionar nem compartilhar o `.env` real.

### 3.2 `public/.htaccess`

Na HostGator a regra precisa de `RewriteBase /`. Sem essa linha, toda rota diferente de `/` retorna erro 500 (inclusive `/login`).

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    RewriteRule ^ /index.php [QSA,L]
</IfModule>
```

Nao usar a diretiva `Options` no `.htaccess`: a hospedagem pode bloquea-la e gerar erro 500.

### 3.3 Cloudflare

| Item | Configuracao |
|---|---|
| Registro DNS | `pdv` apontando para a hospedagem HostGator, com proxy ativo (nuvem laranja) |
| IP de destino | IP compartilhado exibido no painel do cPanel (nao usar o hostname de FTP) |
| HTTPS | Certificado da Cloudflare na borda; AutoSSL ativo no cPanel para o subdominio |

O sistema detecta HTTPS atras do proxy pelo cabecalho `X-Forwarded-Proto` (`index.php`), garantindo que a `BASEURL` seja gerada com `https://`.

Erro Cloudflare 1016 significa que o registro DNS aponta para um destino que nao existe. Corrigir o registro `pdv`.

## 4. Banco de dados em producao

O banco da HostGator foi criado a partir de `Database/database_hostgator.sql`, que contem **apenas a estrutura** das tabelas e nao inclui os recursos de catalogo. Por isso foram aplicados scripts complementares, mantidos em `deploy-hostgator/` (fora do repositorio):

| Script | Finalidade |
|---|---|
| `atualizacao_catalogo_segura.sql` | Cria `catalogo_permissoes`, `produtos_historico` e colunas `codigo_interno`, `preco_compra`, `preco_minimo` em `produtos` |
| `dados_pdv.sql` | Dados iniciais exportados do ambiente local (empresa, perfis, usuarios, produtos) |
| `corrigir_usuarios.sql` / `corrigir_deleted_at.sql` | Ajustes da tabela `usuarios` (ver 4.1) |

As tabelas `pedidos` e `produtos_pedidos` foram criadas manualmente pela aba SQL.

### 4.1 Diferencas do esquema legado (importante)

O esquema da HostGator veio de uma versao antiga do banco e tem diferencas em relacao ao ambiente local:

| Problema | Efeito | Correcao aplicada |
|---|---|---|
| `usuarios.password` era `VARCHAR(50)` | Senhas bcrypt (60 caracteres) eram cortadas e nenhum login funcionava | Coluna alterada para `VARCHAR(255)` |
| `usuarios.deleted_at` era `NOT NULL` | Usuarios ativos ficavam com data preenchida e eram tratados como excluidos (catalogo e listas de permissao nao funcionavam) | Coluna alterada para `NULL DEFAULT NULL` e valores limpos |
| MySQL 5.7 sem `explicit_defaults_for_timestamp` | Gravar `NULL` em `TIMESTAMP NOT NULL` grava a data atual | Evitar colunas de data `NOT NULL` sem necessidade |
| phpMyAdmin bloqueia `information_schema` em scripts preparados | Scripts com `PREPARE` falham com erro #1044 | Executar comandos simples, um por vez, pela aba SQL |

Recomendacao: gerar um novo `database_hostgator.sql` a partir do esquema local atual (`.docker/db/init/01_schema.sql` + migracoes) antes de uma nova instalacao.

### 4.2 Exclusao de usuarios

`usuarios` e referenciada por `vendas`, `caixa_sessoes` e `log_acessos`. Usuarios com historico devem ser **desativados** (`deleted_at` preenchido e `status = 0`), e nao apagados. Exclusao fisica so para dados de teste, apagando antes os registros dependentes e com backup.

## 5. Como publicar uma atualizacao

1. Fazer backup: arquivos alterados (Gerenciador de arquivos) e banco (phpMyAdmin > Exportar).
2. Enviar apenas os arquivos alterados para o mesmo caminho dentro de `public_html/pdv/`.
3. Se houver mudanca de banco, aplicar o script SQL no phpMyAdmin com o banco do PDV selecionado (nunca `information_schema`).
4. Se houver mudanca em CSS/JS, atualizar `$assetVersion` em `App/Views/Layouts/default.php` e `login.php` para evitar cache do navegador e da Cloudflare.
5. Testar: tela de login, login de cada perfil, `/catalogo` e o menu no celular.

Para uma instalacao completa, gerar o pacote `pdv-hostgator.zip` sem `.env`, `.docker`, `.git`, `tests` e `backups`, extrair em `public_html/pdv/` e configurar o `.env`.

## 6. Diagnostico rapido

| Sintoma | Causa provavel | Acao |
|---|---|---|
| Erro 500 do Apache em qualquer rota exceto `/` | `.htaccess` sem `RewriteBase /` | Corrigir `public/.htaccess` (3.2) |
| "E-mail ou senha invalidos" com usuario existente | Senha cortada ou usuario inexistente no banco | Conferir tamanho de `password` (60) |
| Item Catalogo nao aparece para vendedor autorizado | `deleted_at` preenchido ou permissao ausente | Conferir `usuarios.deleted_at` e `catalogo_permissoes` |
| Cloudflare 1016 | DNS do `pdv` aponta para destino inexistente | Ajustar registro no Cloudflare |
| Pagina sem estilo | CSS antigo em cache ou caminho incorreto | Atualizar `$assetVersion` |
| Detalhe de erros PHP | Exibicao desativada em producao | cPanel > Erros ou arquivo `error_log` |
