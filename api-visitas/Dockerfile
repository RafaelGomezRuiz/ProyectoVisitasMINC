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

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar código fuente
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

# Crear estructura base
WORKDIR /var/www/html

# Copiar aplicación Laravel desde la etapa anterior
COPY --from=build /var/www/html /var/www/html

# Copiar configuraciones desde carpeta docker/
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /start.sh

# Permisos
RUN chmod +x /start.sh && chown -R www-data:www-data /var/www/html

# Variables de entorno
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV PORT=8080

# Exponer puerto que Azure espera
EXPOSE 8080

# Comando de inicio (ejecuta Nginx + PHP-FPM con Supervisor)
CMD ["/start.sh"]
