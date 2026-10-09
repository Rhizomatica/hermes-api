FROM php:8.2-alpine

WORKDIR /var/www

# Instalar dependências COM oniguruma
RUN apk add --no-cache \
    sqlite \
    sqlite-dev \
    curl \
    git \
    zip \
    unzip \
    oniguruma-dev \
    libzip-dev

# Instalar extensões PHP (mbstring precisa de oniguruma)
RUN docker-php-ext-install pdo pdo_sqlite mbstring zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar aplicação
COPY . .

# Criar diretórios necessários e configurar permissões
RUN mkdir -p storage bootstrap/cache public/inbox public/outbox && \
    chmod -R 775 storage bootstrap/cache && \
    chmod 775 public/inbox public/outbox

EXPOSE 8000

# Script de entrada simples
CMD ["sh", "-c", "php -S 0.0.0.0:8000 -t public"]