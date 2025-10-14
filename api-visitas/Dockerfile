# ============================================================
# Etapa 1: Build de dependencias PHP
# ============================================================
FROM php:8.2-fpm AS build

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl supervisor nginx \
    && docker-php-ext-install pdo pdo_mysql zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Instalar dependencias de Laravel (sin dev)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && composer clear-cache

# Asegurar que existan carpetas y permisos
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# ============================================================
# Etapa 2: Producción
# ============================================================
FROM php:8.2-fpm

# Instalar Nginx y Supervisor
RUN apt-get update && apt-get install -y nginx supervisor && rm -rf /var/lib/apt/lists/*

# Crear rutas necesarias
RUN mkdir -p /run/php /var/log/nginx /var/www/html/public && chown -R www-data:www-data /run/php

# Copiar archivos desde la etapa build
COPY --from=build /var/www/html /var/www/html
COPY --from=build /usr/bin/composer /usr/bin/composer
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Configurar PHP-FPM para escuchar en 9000
RUN sed -i 's|listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/www.conf

ENV PORT=80
EXPOSE 80

# Ejecutar Supervisor como proceso principal
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
