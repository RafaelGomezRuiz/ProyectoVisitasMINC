#!/bin/sh
set -e

echo "🚀 Iniciando contenedor..."
cd /var/www/html

# ================== PASO CRÍTICO ==================
# Crear el directorio para el PID de Nginx y asignar permisos.
echo "Asegurando el directorio PID para Nginx..."
mkdir -p /run/nginx
chown -R www-data:www-data /run/nginx
echo "✅ Directorio PID listo."
# ==================================================

# Limpiar y regenerar la caché
echo "Optimizando Laravel para producción..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
echo "✅ Optimización completada."

# Arrancar supervisord
echo "Iniciando servicios con Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
