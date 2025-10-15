# =========================================================
# Etapa base: PHP + Nginx + Supervisor para Laravel en Azure
# =========================================================
FROM php:8.2-fpm

# Instalar dependencias del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor git unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd bcmath opcache \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuración de Nginx, Supervisor y Start Script
COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar código de Laravel
COPY . .

# Asignar permisos a www-data
RUN chown -R www-data:www-data /var/www/html /run /var/lib/nginx /var/log/nginx

# Exponer puerto 80 para Azure
EXPOSE 80

# Comando de inicio
CMD ["/start.sh"]
