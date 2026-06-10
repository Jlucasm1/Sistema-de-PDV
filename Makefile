# 
# PDV MASTER — Makefile de Comandos Docker
# 

.PHONY: up down restart build logs shell db composer-install composer-update status help

## Sobe todos os containers
up:
	docker compose up -d

## Sobe containers e reconstrói imagens
build:
	docker compose up -d --build

## Para todos os containers
down:
	docker compose down

## Reinicia os containers
restart:
	docker compose restart

## Exibe logs em tempo real
logs:
	docker compose logs -f

## Acessa o shell do container da aplicação
shell:
	docker compose exec app bash

## Acessa o MySQL via CLI
db:
	docker compose exec db mysql -u pdv -ppdvmaster pdv_master

## Instala as dependências do Composer
composer-install:
	docker compose exec app composer install --no-interaction --optimize-autoloader

## Atualiza as dependências do Composer
composer-update:
	docker compose exec app composer update

## Verifica o status dos containers
status:
	docker compose ps

## Limpa volumes e recria tudo do zero
reset:
	docker compose down -v
	docker compose up -d --build

## Exibe todos os comandos disponíveis
help:
	@grep -E '^## ' Makefile | sed 's/## //'
