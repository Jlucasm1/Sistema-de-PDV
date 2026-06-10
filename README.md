# PDV UniGoiás

Sistema de Ponto de Venda profissional desenvolvido em PHP 8.1, com ambiente Docker Desktop completo e banco de dados MySQL 8.

---

## 📋 Pré-requisitos

| Ferramenta | Versão mínima |
|---|---|
| [Docker Desktop](https://www.docker.com/products/docker-desktop) | 4.x |
| [VS Code](https://code.visualstudio.com/) | 1.80+ |
| Git | 2.x |

---

## 🚀 Instalação em 3 passos

### 1. Clone e configure o ambiente
```bash
git clone <seu-repositorio> pdv-unigoias
cd pdv-unigoias

# Copiar variáveis de ambiente
cp .env.example .env
```

### 2. Suba os containers
```bash
docker-compose up -d --build
```
> ⏳ Na primeira vez, aguarde ~2 min para o banco inicializar e o Composer instalar as dependências.

### 3. Acesse o sistema

| Serviço | URL | Credenciais |
|---|---|---|
| **PDV UniGoiás** | http://localhost:8080 | admin@pdvmaster.com.br / admin123 |
| **PHPMyAdmin** | http://localhost:8081 | pdvmaster / pdvmaster@2024 |

---

## 🔧 Comandos Docker úteis

```bash
# Subir os containers
docker-compose up -d

# Parar os containers
docker-compose down

# Ver logs da aplicação
docker logs pdv_master_app -f

# Ver logs do banco
docker logs pdv_master_db -f

# Acessar shell do container PHP
docker exec -it pdv_master_app bash

# Instalar dependências manualmente
docker exec pdv_master_app composer install

# Rebuild completo
docker-compose down -v && docker-compose up -d --build
```

---

## 📁 Estrutura do Projeto

```
pdv-master/
├ .docker/
│   ├ web/              # PHP 8.1 + Apache
│   │   ├ Dockerfile
│   │   ├ api.conf      # VirtualHost Apache
│   │   ├ entrypoint.sh
│   │   └ conf.d/php.ini
│   └ db/
│       └ init/         # SQLs executados na criação do banco
│           ├ 01_schema.sql
│           └ 02_seed.sql
├ .vscode/              # Configurações VS Code
├ App/
│   ├ Config/           # ConfigPerfil (IDs dos perfis)
│   ├ Controllers/      # Controllers MVC
│   ├ Models/           # Models PDO
│   ├ Repositories/     # Queries complexas
│   ├ Rules/            # Regras de negócio (Auth, PDV)
│   ├ Services/         # Serviços (Upload, Email, Relatório)
│   └ Views/            # Templates PHP
├ Database/             # Migrations e dumps avulsos
├ System/               # Framework interno
│   ├ Auth/             # Trait de autenticação
│   ├ Database/         # Conexão PDO singleton
│   ├ Helpers/          # Funções globais
│   ├ Model/            # Model base
│   ├ NativeQuery/      # ORM nativo (CRUD, transações)
│   ├ Route/            # Roteador
│   ├ Session/          # Sessão + CSRF
│   └ View/             # Motor de views
├ public/               # Assets públicos
├ routes/routes.php     # Todas as rotas
├ docker-compose.yml
├ composer.json
├ .env.example
└ index.php             # Front controller
```

---

## 👤 Perfis de Usuário

| ID | Perfil | Acesso |
|---|---|---|
| 1 | Super Admin | Total (todas empresas) |
| 2 | Administrador | Total (empresa própria) |
| 3 | Operador | PDV + Produtos + Clientes |
| 4 | Vendedor | Somente PDV |
| 5 | Gerente | PDV + Relatórios |

---

## 🛠️ Extensões recomendadas para VS Code

O arquivo `.vscode/extensions.json` já contém as recomendações. Ao abrir o projeto no VS Code, ele sugerirá a instalação automática:

- **PHP Intelephense** — autocomplete e análise PHP
- **Docker** — gerenciar containers pelo VS Code
- **Remote Containers** — abrir projeto dentro do container
- **GitLens** — histórico Git avançado
- **DotENV** — syntax highlight no `.env`
- **Thunder Client** — testar APIs dentro do VS Code

---

## 🔐 Segurança

- Senhas armazenadas com SHA1 + salt configurável via `.env`
- CSRF token disponível via `Token::field()`
- Sessão com `httponly` e `samesite=Lax`
- Conexão PDO com `ERRMODE_EXCEPTION`
- Soft delete em todas as entidades principais

---

## 📊 Banco de Dados

O banco é criado automaticamente pelo Docker no primeiro `up`. Os arquivos em `.docker/db/init/` são executados em ordem:
1. `01_schema.sql` — cria todas as tabelas
2. `02_seed.sql` — insere dados iniciais e usuário admin

**Para resetar o banco:**
```bash
docker-compose down -v
docker-compose up -d
```

---

---

## Simulacao Fiscal (Mock) e Nota Fiscal

Ao finalizar uma compra no PDV, o sistema agora simula impostos e emite uma **nota fiscal mock** automaticamente.

### O que foi adicionado

- Simulador de impostos (mock) com:
  - ICMS (18%)
  - PIS (1,65%)
  - COFINS (7,6%)
- Emissao de NF com:
  - Modelo
  - Serie
  - Numero da NF
  - Chave de acesso simulada
  - Protocolo de autorizacao mock
  - Itens da venda
  - Base de calculo
  - Total de impostos e total da nota

### Fluxo no sistema

- **PDV Diferencial**:
  - Apos confirmar venda, abre modal com a nota fiscal mock
  - Possui botão de impressão
- **PDV Padrão**:
  - Nota fiscal e gerada apos venda rapida
  - Modal abre automaticamente no retorno da página
  - Possui botão de impressão

### Arquivos principais

- `App/Services/Fiscal/ImpostoMockerInterface.php`
- `App/Services/Fiscal/MokitoImpostoMocker.php`
- `App/Services/Fiscal/NotaFiscalEmitter.php`
- `App/Services/Fiscal/CupomFiscalEmitter.php`
- `App/Controllers/PdvDiferencialController.php`
- `App/Controllers/PdvPadraoController.php`
- `public/js/venda/funcoesPdvAvancado.js`
- `App/Views/pdv/diferencial.php`
- `App/Views/pdv/padrao.php`

### Observacao importante

Esta implementação é de **homologação/desenvolvimento** e não substitui integração fiscal oficial (SEFAZ/NFC-e/SAT).
