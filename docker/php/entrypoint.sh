#!/usr/bin/env bash
# Entrypoint de la imagen: valida las variables de entorno que el contenedor
# necesita para hablar con la base de datos externa, prepara los directorios
# de runtime y cede el control a supervisor (nginx + php-fpm + queue + scheduler).
#
# Todo se configura por variables de entorno (`docker run -e VAR=valor`): la
# imagen NO incluye base de datos ni lee ningun .env.
set -e

# --- Variables obligatorias -------------------------------------------------
missing=""
for var in DB_HOST DB_DATABASE DB_USERNAME DB_PASSWORD; do
    if [ -z "${!var}" ]; then
        missing="$missing $var"
    fi
done

if [ -n "$missing" ]; then
    echo "ERROR: faltan variables de entorno obligatorias:$missing" >&2
    echo "Ejemplo: docker run -e DB_HOST=... -e DB_DATABASE=... -e DB_USERNAME=... -e DB_PASSWORD=... -e APP_KEY=... -e JWT_SECRET=... imagen" >&2
    exit 1
fi

# --- Secretos ---------------------------------------------------------------
# Sin APP_KEY/JWT_SECRET el contenedor arranca igual, pero con valores efimeros:
# las sesiones y los tokens dejan de valer en cuanto se reinicia.
if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    echo "ADVERTENCIA: APP_KEY vacio. Se genero una llave efimera; pasa -e APP_KEY=... para que persista." >&2
fi

if [ -z "$JWT_SECRET" ]; then
    export JWT_SECRET="$(php -r 'echo bin2hex(random_bytes(32));')"
    echo "ADVERTENCIA: JWT_SECRET vacio. Se genero uno efimero; los tokens no seran validos entre reinicios." >&2
fi

# --- Directorios de runtime -------------------------------------------------
# storage/ suele venir de un volumen: puede llegar vacio o con otro dueño.
mkdir -p /run/nginx /var/lib/nginx/tmp \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public
chown -R www-data:www-data /var/lib/nginx /var/www/html/storage /var/www/html/bootstrap/cache

# Los assets de la imagen mandan sobre lo que hubiera en public/ (montajes).
if [ -d /var/www/html/public-dist ]; then
    cp -a /var/www/html/public-dist/. /var/www/html/public/
fi

exec "$@"
