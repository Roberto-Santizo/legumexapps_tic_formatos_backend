#!/usr/bin/env bash
# Espera a que postgres (dentro del propio contenedor) acepte conexiones.
set -e

retries="${DB_WAIT_RETRIES:-60}"

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
        echo "No se pudo conectar a la base de datos interna." >&2
        exit 1
    fi
    sleep 1
done
