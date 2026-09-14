#!/usr/bin/env bash
# Espera a que el postgres externo (DB_HOST:DB_PORT) acepte conexiones.
set -e

retries="${DB_WAIT_RETRIES:-60}"

until php -r '
    $dsn = sprintf("pgsql:host=%s;port=%s;dbname=%s",
        getenv("DB_HOST"),
        getenv("DB_PORT") ?: "5432",
        getenv("DB_DATABASE")
    );
    new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
' >/dev/null 2>&1; do
    retries=$((retries - 1))
    if [ "$retries" -le 0 ]; then
        echo "No se pudo conectar a la base de datos en ${DB_HOST}:${DB_PORT:-5432}/${DB_DATABASE}." >&2
        exit 1
    fi
    echo "Esperando a la base de datos en ${DB_HOST}:${DB_PORT:-5432}..."
    sleep 2
done
