# =========================================================
#  Etapa 1: Build de dependencias PHP
# =========================================================
FROM php:8.2-fpm AS build

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev zip curl \
    && docker-php-ext-install pdo pdo_mysql zip gd bcmath

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .
RUN composer install --no-dev --optimize-autoloader

# =========================================================
#  Etapa 2: Imagen final con Nginx + PHP-FPM
# =========================================================
FROM nginx:1.25-alpine

# Instalar PHP-FPM
RUN apk add --no-cache php82 php82-fpm php82-opcache php82-mysqli php82-pdo_mysql php82-mbstring php82-xml php82-json php82-curl php82-tokenizer php82-fileinfo php82-session php82-dom

# Copiar configuración Nginx
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# Copiar código de la app desde el build
WORKDIR /var/www
COPY --from=build /var/www /var/www

# Configuración PHP-FPM
RUN mkdir -p /run/php && chown -R nginx:nginx /run/php /var/www

EXPOSE 80
CMD ["sh", "-c", "php-fpm82 -D && nginx -g 'daemon off;'"]
