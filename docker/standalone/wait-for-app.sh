#!/usr/bin/env bash
# La cola y el scheduler no deben arrancar antes de que app-start haya migrado:
# si no, revientan buscando las tablas de cache/jobs.
set -e

retries="${APP_WAIT_RETRIES:-120}"

until [ -f /run/app-ready ]; do
    retries=$((retries - 1))
    if [ "$retries" -le 0 ]; then
        echo "La aplicacion no termino de migrar a tiempo." >&2
        exit 1
    fi
    sleep 1
done
