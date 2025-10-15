# =========================
# Base PHP + NGINX + Supervisor
# =========================
FROM php:8.3-fpm

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    nginx supervisor \
 && docker-php-ext-install pdo pdo_mysql zip bcmath gd \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar código del proyecto
COPY . /var/www/html

# Instalar dependencias PHP (sin dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permisos correctos para Laravel
# 👇👇👇 LÍNEA CORREGIDA 👇👇👇
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# NGINX y Supervisord configs
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Exponer puerto
EXPOSE 8080

# Comando de arranque
CMD ["/start.sh"]
