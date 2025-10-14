# ============================================================
# Etapa 1: Build de dependencias PHP y Composer
# ============================================================
FROM php:8.2-fpm AS build

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl supervisor nginx \
    && docker-php-ext-install pdo pdo_mysql zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# ============================================================
# Etapa 2: Producción
# ============================================================
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y nginx supervisor && rm -rf /var/lib/apt/lists/*

# Copiar aplicación y configuraciones
COPY --from=build /var/www/html /var/www/html
COPY --from=build /usr/bin/composer /usr/bin/composer
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Configurar PHP para logs visibles
RUN mkdir -p /run/php && chown -R www-data:www-data /run/php && \
    echo "error_log = /proc/self/fd/2" > /usr/local/etc/php/conf.d/error-logging.ini && \
    echo "log_errors = On" >> /usr/local/etc/php/conf.d/error-logging.ini

# Variables y puertos
ENV PORT=80
EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
