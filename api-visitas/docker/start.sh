#!/bin/sh
set -e

echo "🚀 Iniciando contenedor..."
cd /var/www/html

# Limpiar y regenerar la caché, ignorando errores para asegurar que el contenedor arranque.
echo "Optimizando Laravel para producción..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
echo "✅ Optimización completada."

# Opcional: Ejecutar migraciones
# echo "Ejecutando migraciones de la base de datos..."
# php artisan migrate --force || true

# Arrancar supervisord
echo "Iniciando servicios con Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
