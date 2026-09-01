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
# Stage 3: runtime php-fpm
# ---------------------------------------------------------------------------
FROM php:8.5-fpm-alpine AS runtime

# Librerías runtime: se quedan en la imagen final.
RUN apk add --no-cache \
        bash \
        libpq \
        icu-libs \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype

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

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint

# public-dist es la copia pristina de public/. El entrypoint la vuelca sobre el
# volumen compartido con nginx en cada arranque, para que los assets de Vite no
# queden desfasados respecto a la imagen tras un rebuild.
RUN chmod +x /usr/local/bin/entrypoint \
    && cp -a public /var/www/html/public-dist \
    && mkdir -p storage/framework/cache/data \
                storage/framework/sessions \
                storage/framework/views \
                storage/logs \
                bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache public public-dist \
    && chmod -R 775 storage bootstrap/cache

USER www-data

# Regenera el manifest de paquetes con el vendor sin dev-deps.
RUN php artisan package:discover --ansi --no-interaction

EXPOSE 9000

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
