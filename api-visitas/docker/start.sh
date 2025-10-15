 #!/bin/sh
 set -e

 echo "🚀 Iniciando contenedor..."

 # ================== PASO DE DIAGNÓSTICO ==================
 echo "🔎 Verificando la configuración de Nginx en /etc/nginx/conf.d/default.conf..."
 cat /etc/nginx/conf.d/default.conf
 echo "====================================================="
 # =========================================================

 cd /var/www/html

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
