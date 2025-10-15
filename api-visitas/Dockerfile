# ============================================================
# Etapa 1: Build de dependencias PHP y Composer
# ============================================================
FROM php:8.2-fpm AS build

# Instalar dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip curl libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libssl-dev libicu-dev libpq-dev zlib1g-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip bcmath gd intl opcache \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar código fuente de la aplicación
COPY . .

# Instalar dependencias de Laravel optimizadas para producción
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ============================================================
# Etapa 2: Imagen final (PHP-FPM + Nginx + Supervisor)
# ============================================================
FROM php:8.2-fpm

LABEL maintainer="MyTeam Technology <devops@myteamtechnology.com>"

# Instalar Nginx, Supervisor y dependencias básicas
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ============================================================
# Configurar PHP-FPM para usar socket UNIX (no TCP)
# ============================================================
RUN sed -i 's|listen = 9000|listen = /run/php/php-fpm.sock|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;listen.owner = www-data|listen.owner = www-data|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;listen.group = www-data|listen.group = www-data|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;listen.mode = 0660|listen.mode = 0660|' /usr/local/etc/php-fpm.d/www.conf

# ============================================================
# Crear estructura base y copiar archivos
# ============================================================
WORKDIR /var/www/html

# Copiar aplicación Laravel desde la etapa de build
COPY --from=build /var/www/html /var/www/html

# Copiar configuraciones personalizadas desde la carpeta docker/
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /start.sh

# Permisos y ejecución
RUN chmod +x /start.sh && chown -R www-data:www-data /var/www/html

# ============================================================
# Variables de entorno
# ============================================================
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV PORT=8080

# ============================================================
# Exponer el puerto que Azure espera
# ============================================================
EXPOSE 8080

# ============================================================
# Comando de inicio (ejecuta Nginx + PHP-FPM vía Supervisor)
# ============================================================
CMD ["/start.sh"]
