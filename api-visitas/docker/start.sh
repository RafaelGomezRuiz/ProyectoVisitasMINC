#!/bin/sh

# Establece el puerto 80 como valor predeterminado si la variable PORT no está definida por Azure.
export PORT=${PORT:-80}

echo "📄 Plantilla de Nginx encontrada. Reemplazando \$PORT por el valor: $PORT"

# Sustituye la variable ${PORT} en la plantilla y crea el archivo de configuración final de Nginx.
envsubst '${PORT}' < /etc/nginx/templates/nginx.conf > /etc/nginx/nginx.conf

echo "🚀 Iniciando Supervisor..."

# Ejecuta el comando principal para iniciar supervisor.
exec /usr/bin/supervisord -c /etc/supervisord.conf
