# ============================================================
# Etapa 1: Build de dependencias PHP
# ============================================================
FROM php:8.3-fpm AS build

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

RUN chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data /var/www/html

# ============================================================
# Etapa 2: Producción
# ============================================================
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y nginx supervisor && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=build /var/www/html /var/www/html
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Crear socket y permisos correctos
RUN mkdir -p /run/php && chown -R www-data:www-data /run/php

# Configurar PHP-FPM para usar el socket correcto
RUN sed -i 's|listen = .*|listen = /run/php/php-fpm.sock|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;listen.owner = .*|listen.owner = www-data|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;listen.group = .*|listen.group = www-data|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;listen.mode = .*|listen.mode = 0660|' /usr/local/etc/php-fpm.d/www.conf

RUN echo "error_log = /proc/self/fd/2" >> /usr/local/etc/php/php.ini

ENV PORT=80
EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
