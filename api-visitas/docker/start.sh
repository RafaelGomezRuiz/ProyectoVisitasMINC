#!/bin/sh

# Sustituye la variable de puerto para Nginx
export PORT=${PORT:-80}
envsubst '${PORT}' < /etc/nginx/templates/nginx.conf > /etc/nginx/nginx.conf

# Navega al directorio de la aplicación
cd /var/www/html

# 👇👇👇 PASO CRÍTICO: LIMPIAR LA CACHÉ ANTES DE ARRANCAR 👇👇👇
# Esto asegura que las variables de entorno de Azure sean leídas.
echo "Limpiando la caché de configuración de Laravel..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo "✅ Caché limpiada."
echo "🚀 Iniciando Supervisor..."

# Ejecuta el comando principal para iniciar supervisor.
exec /usr/bin/supervisord -c /etc/supervisord.conf
