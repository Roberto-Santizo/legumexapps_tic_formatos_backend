#!/usr/bin/env bash
# Arranca php-fpm, pero sólo después de migrar contra la base interna.
set -e

rm -f /run/app-ready

wait-for-db

su-exec www-data php /var/www/html/artisan migrate --force --no-interaction

# Usuario administrador inicial. El seeder es idempotente, asi que correrlo en
# cada arranque no duplica ni pisa nada.
su-exec www-data php /var/www/html/artisan db:seed --class='Database\Seeders\InitialUserSeeder' --force --no-interaction

if [ "$APP_ENV" != "local" ]; then
    su-exec www-data php /var/www/html/artisan config:cache
    su-exec www-data php /var/www/html/artisan route:cache
    su-exec www-data php /var/www/html/artisan view:cache
fi

# Señal para queue/scheduler: el esquema ya está listo.
touch /run/app-ready

exec php-fpm --nodaemonize
