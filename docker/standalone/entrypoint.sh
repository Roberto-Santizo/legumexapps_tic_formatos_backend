#!/usr/bin/env bash
# Entrypoint de la imagen all-in-one: prepara el cluster de postgres y los
# secretos persistentes, y luego cede el control a supervisor.
set -e

PGDATA="${PGDATA:-/var/lib/postgresql/data}"
PERSIST_DIR="$(dirname "$PGDATA")"
SECRETS_FILE="$PERSIST_DIR/app-secrets.env"

# --- Directorios de runtime -------------------------------------------------
mkdir -p /run/postgresql /run/nginx /var/lib/nginx/tmp "$PERSIST_DIR"
chown -R postgres:postgres /run/postgresql "$PERSIST_DIR"
chown -R www-data:www-data /var/lib/nginx /var/www/html/storage /var/www/html/bootstrap/cache

# Los assets de la imagen mandan sobre lo que hubiera en public/ (montajes).
if [ -d /var/www/html/public-dist ]; then
    cp -a /var/www/html/public-dist/. /var/www/html/public/
fi

# --- Cluster de postgres ----------------------------------------------------
if [ ! -s "$PGDATA/PG_VERSION" ]; then
    echo "Inicializando cluster de postgres en $PGDATA..."
    mkdir -p "$PGDATA"
    chown postgres:postgres "$PGDATA"
    chmod 700 "$PGDATA"
    su-exec postgres initdb -D "$PGDATA" -U postgres --auth-local=trust --auth-host=scram-sha-256 >/dev/null
fi

# La base sólo escucha en loopback: no se expone fuera del contenedor.
cat > "$PGDATA/postgresql.auto.conf" <<'PGCONF'
listen_addresses = '127.0.0.1'
port = 5432
PGCONF

cat > "$PGDATA/pg_hba.conf" <<'PGHBA'
local   all   all                 trust
host    all   all   127.0.0.1/32  scram-sha-256
host    all   all   ::1/128       scram-sha-256
PGHBA

chown postgres:postgres "$PGDATA/postgresql.auto.conf" "$PGDATA/pg_hba.conf"
chmod 600 "$PGDATA/postgresql.auto.conf" "$PGDATA/pg_hba.conf"

# Servidor temporal (sólo socket unix) para crear rol y base si hacen falta y
# dejar la contraseña alineada con DB_PASSWORD en cada arranque.
echo "Preparando rol y base de datos..."
su-exec postgres pg_ctl -D "$PGDATA" -o "-c listen_addresses=''" -w -t 60 start >/dev/null

db_user="${DB_USERNAME:-postgres}"
db_name="${DB_DATABASE:-legumexapps_tic_formatos}"
db_pass="${DB_PASSWORD:-mysecretpassword}"

role_exists="$(su-exec postgres psql -tAc "SELECT 1 FROM pg_roles WHERE rolname = '${db_user}'")"
if [ "$role_exists" != "1" ]; then
    su-exec postgres psql -q -c "CREATE ROLE \"${db_user}\" LOGIN SUPERUSER" >/dev/null
fi
su-exec postgres psql -q -c "ALTER ROLE \"${db_user}\" WITH PASSWORD '${db_pass}'" >/dev/null

db_exists="$(su-exec postgres psql -tAc "SELECT 1 FROM pg_database WHERE datname = '${db_name}'")"
if [ "$db_exists" != "1" ]; then
    su-exec postgres createdb -O "${db_user}" "${db_name}"
fi

su-exec postgres pg_ctl -D "$PGDATA" -w -t 60 stop >/dev/null

# --- Secretos persistentes --------------------------------------------------
# Se guardan junto a los datos: si el volumen sobrevive, las sesiones y los
# tokens siguen siendo validos tras reiniciar el contenedor.
# Precedencia: variables de entorno del usuario > fichero persistido > nuevas.
if [ -f "$SECRETS_FILE" ]; then
    stored_app_key="$(sed -n "s/^APP_KEY='\(.*\)'$/\1/p" "$SECRETS_FILE")"
    stored_jwt_secret="$(sed -n "s/^JWT_SECRET='\(.*\)'$/\1/p" "$SECRETS_FILE")"
    APP_KEY="${APP_KEY:-$stored_app_key}"
    JWT_SECRET="${JWT_SECRET:-$stored_jwt_secret}"
fi

if [ -z "$APP_KEY" ]; then
    APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    echo "APP_KEY generada y guardada en el volumen de datos."
fi

if [ -z "$JWT_SECRET" ]; then
    JWT_SECRET="$(php -r 'echo bin2hex(random_bytes(32));')"
    echo "JWT_SECRET generado y guardado en el volumen de datos."
fi

export APP_KEY JWT_SECRET PGDATA

umask 077
cat > "$SECRETS_FILE" <<SECRETS
APP_KEY='${APP_KEY}'
JWT_SECRET='${JWT_SECRET}'
SECRETS
chown postgres:postgres "$SECRETS_FILE"
umask 022

exec "$@"
