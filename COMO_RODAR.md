# 🚀 PDV MASTER — Como Rodar no VS Code + Docker

## 📋 Pré-requisitos

| Ferramenta | Download |
|---|---|
| **Docker Desktop** | https://www.docker.com/products/docker-desktop/ |
| **VS Code** | https://code.visualstudio.com/ |
| **Extensão Docker** (opcional, recomendada) | VS Code → Extensions → pesquise "Docker" (Microsoft) |
| **Extensão Dev Containers** (opcional) | VS Code → Extensions → pesquise "Dev Containers" |

---

## 🔐 Credenciais do Banco de Dados

Já configuradas no arquivo `.env`:

| Campo | Valor |
|---|---|
| **Usuário** | `pdvmaster` |
| **Senha** | `pdvmaster@2024` |
| **Banco de dados** | `pdvmaster` |
| **Senha root MySQL** | `root@pdvmaster2024` |

---

## 🔑 Credenciais de Acesso ao Sistema

| Campo | Valor |
|---|---|
| **E-mail** | `admin@pdvmaster.com.br` |
| **Senha** | `admin123` |

> ⚠️ **Troque a senha após o primeiro login!**

---

## ▶️ Passo a Passo para Rodar

### 1. Abrir no VS Code
```
Arquivo → Abrir Pasta → selecione a pasta pdv-master
```

### 2. Abrir o terminal integrado do VS Code
```
Menu Terminal → Novo Terminal
(ou pressione: Ctrl + ` )
```

### 3. Garantir que o Docker Desktop está rodando
- Abra o **Docker Desktop** pelo menu iniciar
- Espere o ícone 🐳 na barra de tarefas parar de girar

### 4. Subir os containers
No terminal do VS Code, execute:
```bash
docker-compose up -d --build
```

Aguarde o download das imagens e a construção (pode demorar alguns minutos na primeira vez).

### 5. Acessar o sistema

| Serviço | URL |
|---|---|
| **Aplicação pdv** | http://localhost:8080 |
| **PHPMyAdmin** (gerenciar banco) | http://localhost:8081 |

---

## 🛑 Parar os containers

```bash
docker-compose down
```

Para parar **e apagar os dados do banco**:
```bash
docker-compose down -v
```

---

## 🐛 Solução de Problemas

### "Access is denied" ou "Cannot connect"
→ Docker Desktop não está aberto. Abra o Docker Desktop e tente novamente.

### Porta 8080 ou 3306 já em uso
→ Edite o `.env` e mude as portas:
```env
PORT_APP=8090
PORT_DB=3307
PORT_PMA=8082
```

### Banco de dados não inicializa
```bash
docker-compose down -v
docker-compose up -d --build
```

### Ver logs de erros
```bash
docker-compose logs -f app
docker-compose logs -f db
```

---

## 📁 Estrutura dos arquivos de configuração

```
pdv-master/
├ .env                    ← ✅ Credenciais e configurações (JÁ CONFIGURADO)
├ docker-compose.yml      ← Orquestração dos containers
├ .docker/
│   ├ web/Dockerfile      ← Imagem PHP + Apache
│   └ db/init/
│       ├ 01_schema.sql   ← Estrutura do banco (criado automaticamente)
│       └ 02_seed.sql     ← Dados iniciais + usuário admin
```
