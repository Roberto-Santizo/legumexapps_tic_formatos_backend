# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1: build de assets (Vite / Tailwind) — welcome.blade.php usa @vite
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2: dependencias PHP (sin dev, autoloader optimizado)
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

# ---------------------------------------------------------------------------
# Stage 3: imagen final — nginx + php-fpm + queue + scheduler bajo supervisor.
# NO incluye base de datos: se conecta a un postgres externo con las variables
# DB_* que se pasan al contenedor (`docker run -e DB_HOST=... -e DB_PASSWORD=...`).
# ---------------------------------------------------------------------------
FROM php:8.5-fpm-alpine AS app

# Librerías runtime: se quedan en la imagen final.
RUN apk add --no-cache \
        bash \
        libpq \
        icu-libs \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        nginx \
        supervisor \
        su-exec

# Headers de compilación: virtual package que se elimina tras compilar,
# así no arrastramos los -dev ni dejamos huérfanas las libs de arriba.
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        postgresql-dev \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        bcmath \
        intl \
        zip \
        gd \
        pcntl \
    && apk del --no-network .build-deps

# OPcache NO se instala como extension: PHP 8.5 lo trae compilado estaticamente
# en el binario (php -m ya lo lista). Solo se configura, y eso vive en php.ini.

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/php/app-start.sh /usr/local/bin/app-start
COPY docker/php/wait-for-db.sh /usr/local/bin/wait-for-db
COPY docker/php/wait-for-app.sh /usr/local/bin/wait-for-app

# public-dist es la copia pristina de public/. El entrypoint la vuelca sobre
# public/ en cada arranque por si alguien monta un volumen encima.
RUN chmod +x /usr/local/bin/entrypoint \
              /usr/local/bin/app-start \
              /usr/local/bin/wait-for-db \
              /usr/local/bin/wait-for-app \
    && cp -a public /var/www/html/public-dist \
    && mkdir -p storage/framework/cache/data \
                storage/framework/sessions \
                storage/framework/views \
                storage/logs \
                storage/app/public \
                bootstrap/cache \
                /run/nginx /var/lib/nginx/tmp \
    && chown -R www-data:www-data storage bootstrap/cache public public-dist /var/lib/nginx \
    && chmod -R 775 storage bootstrap/cache \
    && ln -sf /etc/supervisor/conf.d/supervisord.conf /etc/supervisord.conf

# Regenera el manifest de paquetes con el vendor sin dev-deps.
RUN su-exec www-data php artisan package:discover --ansi --no-interaction

# Valores por defecto de lo que NO es secreto ni depende del entorno. Todo lo
# demas (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_KEY, JWT_SECRET,
# APP_URL, ADMIN_*) se pasa con `docker run -e VAR=valor` o `--env-file`.
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_LOCALE=en \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=info \
    DB_CONNECTION=pgsql \
    DB_PORT=5432 \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    FILESYSTEM_DISK=local

# Firmas subidas (storage/app/public), sesiones y logs: montar un volumen aqui
# para no perderlas al recrear el contenedor.
VOLUME ["/var/www/html/storage"]

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/up") === false ? 1 : 0);'

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
