FROM php:8.1-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    locales \
    tzdata \
    dos2unix \
    iproute2 \
    nano \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Configurar locale PT-BR e Timezone
RUN echo "pt_BR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen "pt_BR.UTF-8" && \
    dpkg-reconfigure --frontend=noninteractive locales && \
    update-locale LANG="pt_BR.UTF-8" && \
    ln -fs /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime && \
    dpkg-reconfigure -f noninteractive tzdata

# Instalar extensões do PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql zip gd mysqli

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite && \
    a2dissite 000-default

# Ajustar usuário www-data para uid 1000 (compatível com host Linux/Mac)
RUN usermod -u 1000 -s /bin/bash www-data && groupmod -g 1000 www-data

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer && \
    mkdir -p /var/www/.composer/cache && \
    chown 1000:1000 /var/www/.composer/cache

# Copiar configurações PHP
COPY conf.d/php.ini /usr/local/etc/php/conf.d/pdv-master.ini

# Copiar e preparar entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN dos2unix /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
