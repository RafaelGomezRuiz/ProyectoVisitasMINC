# ============================================================
# Etapa 1: Build de dependencias PHP y Node
# ============================================================
FROM php:8.3-fpm AS build

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl supervisor nginx \
    && docker-php-ext-install pdo pdo_mysql zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Definir directorio de trabajo
WORKDIR /var/www/html
COPY . .

# Instalar dependencias de Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Permisos necesarios
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Copiar configuraciones
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# ============================================================
# Etapa 2: Producción
# ============================================================
FROM php:8.3-fpm

# Instalar Nginx y Supervisor
RUN apt-get update && apt-get install -y nginx supervisor && rm -rf /var/lib/apt/lists/*

# Copiar aplicación desde la etapa build
COPY --from=build /var/www/html /var/www/html
COPY --from=build /usr/bin/composer /usr/bin/composer
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Configurar PHP-FPM y logs
RUN mkdir -p /run/php && chown -R www-data:www-data /run/php
RUN echo "error_log = /proc/self/fd/2" >> /usr/local/etc/php/php.ini

# Exponer puerto
ENV PORT=80
EXPOSE 80

# Comando de inicio
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
