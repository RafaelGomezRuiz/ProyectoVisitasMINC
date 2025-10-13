# =========================================================
# Etapa final de ejecución
# =========================================================
FROM php:8.2-fpm

# Instalar Nginx y utilidades básicas
RUN apt-get update && apt-get install -y nginx supervisor vim openssh-server \
    && mkdir -p /run/sshd

# Copiar el código de la aplicación
WORKDIR /var/www/html
COPY . .

# Configurar Nginx
COPY ./docker/nginx.conf /etc/nginx/nginx.conf

# Permisos de Laravel
RUN chmod -R 775 storage bootstrap/cache

# Configurar SSH para App Service
RUN echo "root:Docker!" | chpasswd
EXPOSE 80 2222
ENV SSH_PORT=2222

# Configurar supervisord para ejecutar PHP-FPM, Nginx y SSH
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
