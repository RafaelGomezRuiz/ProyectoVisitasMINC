#!/bin/sh
set -e

echo "🚀 Iniciando contenedor..."
cd /var/www/html

# <--- CAMBIO CLAVE: Limpiar y REGENERAR la caché para usar las variables de entorno de Azure.
echo "Optimizando Laravel para producción..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Optimización completada."

# Arrancar supervisord
echo "Iniciando servicios con Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
