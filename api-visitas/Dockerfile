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

# 👇 CAMBIO 1: Añadimos 'gettext' para tener el comando 'envsubst'
RUN apt-get update && apt-get install -y nginx supervisor gettext && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=build /var/www/html /var/www/html

# 👇 CAMBIO 2: Copiamos nginx.conf a una carpeta de plantillas
COPY docker/nginx.conf /etc/nginx/templates/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# 👇 CAMBIO 3: Copiamos nuestro nuevo script de inicio y le damos permisos de ejecución
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Crear socket y permisos correctos
RUN mkdir -p /var/run/php && chown -R www-data:www-data /var/run/php

# Forzar PHP-FPM a usar el socket /var/run/php/php-fpm.sock
RUN sed -i 's|listen = .*|listen = /var/run/php/php-fpm.sock|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;listen.owner = .*|listen.owner = www-data|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;listen.group = .*|listen.group = www-data|' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's|;listen.mode = .*|listen.mode = 0660|' /usr/local/etc/php-fpm.d/www.conf

RUN echo "error_log = /proc/self/fd/2" >> /usr/local/etc/php/php.ini

# Ya no es necesario ENV PORT=80 porque Azure lo proporciona.
# EXPOSE 80 sigue siendo una buena práctica para documentar el puerto interno.
EXPOSE 80

# 👇 CAMBIO 4: Cambiamos el comando de inicio para usar nuestro script.
CMD ["/usr/local/bin/start.sh"]
