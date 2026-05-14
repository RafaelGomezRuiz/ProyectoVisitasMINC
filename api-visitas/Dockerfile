# ============================================================
# Etapa 1: Build de dependencias PHP y Composer
# ============================================================
FROM php:8.2-fpm AS build

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip curl libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libssl-dev libicu-dev libpq-dev zlib1g-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip bcmath gd intl opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Instalar dependencias Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan config:clear || true

# ============================================================
# Etapa 2: Imagen final (PHP-FPM + Nginx + Supervisor)
# ============================================================
FROM php:8.2-fpm

LABEL maintainer="Dirección de Tecnología - Ministerio de Cultura <tecnologia@cultura.gob.do>"

RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Crear directorios necesarios para runtime
RUN mkdir -p /run/nginx /run/php

WORKDIR /var/www/html

# Copiar todo desde la etapa build, incluyendo vendor/
COPY --from=build /var/www/html /var/www/html

# Copiar configuraciones
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /start.sh

RUN chmod +x /start.sh && chown -R www-data:www-data /var/www/html

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV PORT=8080

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD curl -f http://localhost:8080/api/health || exit 1

CMD ["/start.sh"]

