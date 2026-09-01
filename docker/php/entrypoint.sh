#!/usr/bin/env bash
set -e

# Sincroniza el volumen compartido con nginx a partir de la imagen actual, para
# que los assets de Vite no queden desfasados tras un rebuild.
if [ -d /var/www/html/public-dist ]; then
    cp -a /var/www/html/public-dist/. /var/www/html/public/
fi

# ¿El comando que vamos a ejecutar necesita la base de datos? Los comandos
# puntuales (php -v, composer, artisan route:list...) no deben quedar colgados.
needs_database() {
    if [ "$RUN_MIGRATIONS" = "true" ]; then
        return 0
    fi

    case "$1" in
        php-fpm)
            return 0
            ;;
    esac

    case "$*" in
        *queue:work*|*queue:listen*|*schedule:work*|*schedule:run*|*migrate*)
            return 0
            ;;
    esac

    return 1
}

wait_for_database() {
    local retries=30

    until php -r '
        $dsn = sprintf("pgsql:host=%s;port=%s;dbname=%s",
            getenv("DB_HOST") ?: "127.0.0.1",
            getenv("DB_PORT") ?: "5432",
            getenv("DB_DATABASE") ?: "postgres"
        );
        new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
    ' >/dev/null 2>&1; do
        retries=$((retries - 1))
        if [ "$retries" -le 0 ]; then
            echo "No se pudo conectar a la base de datos." >&2
            exit 1
        fi
        echo "Esperando a la base de datos..."
        sleep 2
    done
}

if needs_database "$@"; then
    wait_for_database
fi

# Sólo el contenedor de php-fpm migra y cachea; queue y scheduler no deben
# competir por las migraciones al arrancar en paralelo.
if [ "$1" = "php-fpm" ] || [ "$RUN_MIGRATIONS" = "true" ]; then
    if [ -z "$APP_KEY" ]; then
        echo "APP_KEY vacio: generando una llave efimera para este contenedor." >&2
        export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    fi

    if [ -z "$JWT_SECRET" ]; then
        echo "ADVERTENCIA: JWT_SECRET vacio. Los tokens no seran validos entre reinicios." >&2
    fi

    php artisan migrate --force --no-interaction

    if [ "$APP_ENV" != "local" ]; then
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
    fi
fi

exec "$@"
