#!/bin/sh
set -e

echo "🚀 Iniciando contenedor..."
cd /var/www/html

# Crear directorios necesarios para Nginx y PHP-FPM
echo "🔧 Preparando directorios..."
mkdir -p /run/nginx /run/php
chown -R www-data:www-data /run/nginx /run/php
echo "✅ Directorios listos."

# Optimizar Laravel si existe .env
if [ -f .env ]; then
  echo "⚙️ Optimizando Laravel..."
  php artisan config:clear || true
  php artisan route:clear || true
  php artisan view:clear || true
  php artisan storage:link || true
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
  echo "✅ Cachés optimizadas."
else
  echo "⚠️ No se encontró archivo .env; se omiten optimizaciones."
fi

# Iniciar Supervisor (que ejecuta PHP-FPM + Nginx)
echo "🚀 Iniciando servicios..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
