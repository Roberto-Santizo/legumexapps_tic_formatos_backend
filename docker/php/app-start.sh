#!/usr/bin/env bash
# Arranca php-fpm, pero sólo después de migrar contra la base de datos externa.
# Lo lanza supervisor; queue y scheduler esperan a /run/app-ready.
set -e

rm -f /run/app-ready

wait-for-db

cd /var/www/html

su-exec www-data php artisan migrate --force --no-interaction

# Usuario administrador inicial. El seeder es idempotente, asi que correrlo en
# cada arranque no duplica ni pisa nada.
su-exec www-data php artisan db:seed --class='Database\Seeders\InitialUserSeeder' --force --no-interaction

# public/storage -> storage/app/public: sin esto nginx no sirve las firmas
# cuando SIGNATURES_DISK=public. Con s3 no estorba, asi que se crea siempre.
su-exec www-data php artisan storage:link --force --no-interaction

if [ "$APP_ENV" != "local" ]; then
    su-exec www-data php artisan config:cache
    su-exec www-data php artisan route:cache
    su-exec www-data php artisan view:cache
fi

# Señal para queue/scheduler: el esquema ya está listo.
touch /run/app-ready

exec php-fpm --nodaemonize
