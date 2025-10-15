#!/bin/sh
set -e

echo "🚀 Iniciando contenedor..."
cd /var/www/html

# Crear directorios necesarios
echo "🔧 Preparando rutas de ejecución..."
mkdir -p /run/nginx /run/php
chown -R www-data:www-data /run/nginx /run/php
echo "✅ Directorios listos."

# Limpiar y regenerar la caché de Laravel
echo "⚡ Optimizando Laravel..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
echo "✅ Cachés optimizadas."

# Iniciar supervisord
echo "🚀 Iniciando servicios..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
