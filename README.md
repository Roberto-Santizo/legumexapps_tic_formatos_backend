# Legumexapps TIC Formatos — backend

API REST (Laravel 13 / PHP 8.5, PostgreSQL, JWT) para los formatos de entrega y devolución de equipo.

## Docker

La imagen `robertosantizo/legumexapps_tic_formatos_backend` levanta **sólo el backend** (nginx + php-fpm + worker de cola + scheduler). No incluye base de datos: se conecta a un PostgreSQL externo que se indica por variables de entorno al arrancar el contenedor. Al iniciar, migra, crea el usuario administrador inicial y cachea la configuración.

```bash
docker run -d --name legumex -p 8000:80 \
  -e APP_URL=http://localhost:8000 \
  -e APP_KEY='base64:...' \
  -e JWT_SECRET='...' \
  -e DB_HOST=host.docker.internal \
  -e DB_PORT=5432 \
  -e DB_DATABASE=legumexapps_tic_formatos \
  -e DB_USERNAME=postgres \
  -e DB_PASSWORD='...' \
  -e ADMIN_USERNAME=admin \
  -e ADMIN_PASSWORD='...' \
  -v legumex_storage:/var/www/html/storage \
  robertosantizo/legumexapps_tic_formatos_backend:latest
```

También se puede pasar un archivo con las variables: `docker run --env-file .env.docker ...`.

### Variables de entorno

| Variable | Obligatoria | Descripción |
|---|---|---|
| `DB_HOST` | sí | Host del PostgreSQL. Para uno en la máquina anfitriona: `host.docker.internal` (en Linux añade `--add-host=host.docker.internal:host-gateway`). |
| `DB_DATABASE` | sí | Nombre de la base (debe existir; las tablas las crean las migraciones). |
| `DB_USERNAME` | sí | Usuario de la base. |
| `DB_PASSWORD` | sí | Contraseña de la base. |
| `DB_PORT` | no | Puerto (por defecto `5432`). |
| `APP_KEY` | recomendada | Llave de la app (`php artisan key:generate --show`). Si falta se genera una efímera y las sesiones se invalidan al reiniciar. |
| `JWT_SECRET` | recomendada | Secreto de los tokens (`php artisan jwt:secret --show`). Si falta se genera uno efímero y los tokens dejan de valer al reiniciar. |
| `APP_URL` | recomendada | URL pública con la que se construyen enlaces (por defecto `http://localhost`). |
| `APP_ENV` / `APP_DEBUG` | no | Por defecto `production` / `false`. |
| `APP_NAME`, `APP_LOCALE`, `LOG_LEVEL` | no | Nombre, idioma (`en`) y nivel de log (`info`). Los logs salen por `docker logs`. |
| `ADMIN_NAME`, `ADMIN_USERNAME`, `ADMIN_PASSWORD` | no | Usuario administrador inicial (`Administrador` / `admin` / `admin123`). El seeder es idempotente y no pisa una contraseña ya cambiada. |
| `DB_WAIT_RETRIES` | no | Intentos (cada 2 s) esperando a la base antes de abortar (`60`). |

Sin las cuatro `DB_*` obligatorias el contenedor termina al instante con un mensaje indicando cuáles faltan.

### Volúmenes

- `/var/www/html/storage`: firmas subidas (`storage/app/public/signatures`), sesiones y logs. Móntalo para no perderlas al recrear el contenedor.

### Con docker compose

`docker-compose.yml` levanta el mismo contenedor leyendo las variables del entorno de la shell o del `.env` del proyecto:

```bash
docker compose up -d --build
```

### Build local

```bash
docker build -f docker/php/Dockerfile -t legumexapps-tic-formatos .
```

### Publicación

Cada push a `main` ejecuta `.github/workflows/docker-publish.yml`, que autoincrementa el tag `v0.0.X`, construye la imagen y la publica en Docker Hub como `:0.0.X` y `:latest`.

## Desarrollo local

```bash
composer setup                 # install + .env + key + migrate + npm build
composer dev                   # server, queue, logs y vite
php artisan test --compact     # suite completa
```

La documentación OpenAPI se sirve en `/api/documentation`.
