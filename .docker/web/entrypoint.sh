#!/bin/sh
set -e

# Instalar dependências via Composer se vendor não existir
if [ ! -d "/var/www/html/vendor" ]; then
    echo "[PDV MASTER] Instalando dependências do Composer..."
    cd /var/www/html && composer install --no-interaction --prefer-dist
fi

# Criar .env a partir do .env.example se não existir
if [ ! -f "/var/www/html/.env" ]; then
    cp /var/www/html/.env.example /var/www/html/.env
    echo "[PDV MASTER] .env criado a partir do .env.example"
fi

# Ajustar permissões de pastas de upload
chmod -R 775 /var/www/html/public/imagem 2>/dev/null || true
chown -R www-data:www-data /var/www/html/public/imagem 2>/dev/null || true

sh /usr/local/bin/docker-php-entrypoint "$@"
