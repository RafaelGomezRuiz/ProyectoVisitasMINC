# =========================================================
# Etapa 1: Build con dependencias
# =========================================================
FROM php:8.2-fpm AS build

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql zip bcmath gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader

# =========================================================
# Etapa 2: Producción
# =========================================================
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y nginx supervisor openssh-server vim && \
    mkdir -p /run/sshd /var/log/supervisor /etc/supervisor/conf.d /var/www/html

COPY --from=build /var/www/html /var/www/html

# Copiar configuraciones
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# Permisos Laravel
RUN chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data /var/www/html

# Variables de entorno para Azure
ENV PORT=80
EXPOSE 80 2222

# Comando principal
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
