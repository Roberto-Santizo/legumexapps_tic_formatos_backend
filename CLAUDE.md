# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>

# Proyecto: Legumexapps TIC Formatos (backend)

API REST en Laravel 13 / PHP 8.5 con autenticación JWT (`tymon/jwt-auth`) y PostgreSQL. Es sólo backend: el único frontend es `welcome.blade.php` y el Swagger UI. Mensajes de API, comentarios y documentación van en español.

Dominio: catálogos (`brands`, `departments`, `employees`, `equipments`, `caracteristics`) y los formatos de **entrega** (`delivery_documents` + `delivery_document_details`) y **devolución** (`return_documents` + `return_document_details`) de equipo, firmados con imágenes. El flujo completo está en `flujo.md`; las reglas de negocio (RN-01…RN-25), ya implementadas, en `reglas.md`.

## Comandos

```bash
composer setup                      # install + .env + key + migrate + npm build
composer dev                        # php artisan dev (server, queue, logs, vite)
php artisan test --compact          # suite completa
vendor/bin/pest tests/Feature/AssignmentFlowTest.php
vendor/bin/pest --filter='crea una marca'
vendor/bin/pint --dirty --format agent   # obligatorio tras tocar PHP
php artisan storage:link            # necesario para servir las firmas
docker compose up -d                # app php-fpm + nginx + queue + scheduler + postgres
```

Tests: sqlite en memoria (`phpunit.xml`). `tests/Pest.php` **no** aplica `RefreshDatabase` globalmente — cada archivo de Feature hace `uses(RefreshDatabase::class)` explícitamente. `AssignmentFlowTest` cubre el flujo entrega → devolución parcial de punta a punta y `ReglasNegocioTest` una regla de negocio por test. La suite completa pasa (80/80).

## Arquitectura

**Rutas.** `routes/api.php` sólo hace `require` de un archivo por recurso (`auth.php`, `brands.php`, `departments.php`, `employees.php`, `equipments.php`, `caracteristics.php`, `delivery_documents.php`, `delivery_document_details.php`, `return_documents.php`, `return_document_details.php`). El prefijo `/api` lo añade `bootstrap/app.php`; no lo repitas en los archivos de rutas. Al añadir un recurso, crea su archivo y encadénalo desde `api.php`. Los archivos nuevos usan `Route::middleware('jwt.auth')->group(...)`; los viejos encadenan `->middleware(['jwt.auth'])` ruta por ruta. Todas las rutas de negocio piden `jwt.auth`. `/equipments/available` se declara **antes** de `/equipments/{id}` o el `{id}` se la come.

**Middleware.** `jwt.auth` viene de tymon; los alias `admin` y `administrate_agricola` se registran en `bootstrap/app.php` y lanzan `UnauthorizedError`. RN-07: `store`/`update`/`delete` de `delivery_documents` y `return_documents` exigen `admin`; las lecturas sólo `jwt.auth`.

**Respuestas y errores.** Todo pasa por `App\Helpers\ResponseHandler`, que envuelve en `{statusCode, message, data}`. Los errores de dominio son subclases de `App\Errors\ApiException` (`NotFoundError`, `BadRequestError`, `UnauthorizedError`, `NotAcceptable`) con su `getStatusCode()`; `bootstrap/app.php` las renderiza globalmente, pero los controllers además envuelven en `try/catch` y devuelven `ResponseHandler::error($th)`. Sigue ese patrón; no devuelvas `response()->json()` a pelo.

**Controllers.** `index/store/show/update`, más `delete` (verbo propio, no `destroy`) en `DeliveryDocumentController` y `DeliveryDocumentDetailController`; los de devolución no borran. Consultas de proceso: `EquipmentController@available` y `@history`, `EmployeeController@equipments`, `DeliveryDocumentController@pendingItems`. Buscan con `Model::find()` y lanzan `NotFoundError` desde un helper privado `find<X>OrFail()`; los de documentos cargan ahí mismo las relaciones de la constante `RELATIONS` de la clase. `BrandController`/`DepartmentController` son el patrón de referencia: FormRequest con `messages()` en español. Todos usan FormRequest.

**FormRequests.** Los de catálogos viven en `app/Http/Requests/<Recurso>/`; los de documentos están sueltos en `app/Http/Requests/` como `Create<X>Request` / `Update<X>Request` (los `Update*` de documentos firmados sólo aceptan observaciones, y el de la entrega además `location`). Los filtros de listado también son FormRequest (`DeliveryDocumentIndexRequest`, `ReturnDocumentIndexRequest`, `EquipmentAvailableRequest`); los de detalles siguen leyendo `$request->query()`. Al tocar un recurso, sigue la ubicación que ya tenga.

**Resources.** `app/Http/Resources/` transforma las salidas de employees, equipments, los cuatro recursos de documentos y `AssignmentResource` (un `DeliveryDocumentDetail` con su entrega y su devolución; la usan `/equipments/{id}/history` y `/employees/{id}/equipments`). Formatean para el cliente (fechas `d-m-Y h:m:s A`, `original` → `Nuevo`/`Usado`, `location` vía `Plant::tryFrom()->label()`). Los catálogos simples devuelven el modelo directo.

**Servicios.** Auth (`AuthServiceInterface` → `AuthService`, en `App\Providers\Auth\AuthProvider`) y Storage (`ImageStorageServiceInterface` → `ImageStorageService`, en `App\Providers\Storage\StorageProvider`); ambos providers están en `bootstrap/providers.php`. Se inyectan como parámetro del método del controller.

**Firmas.** `responsable_signature` y `administrador_signature` llegan como archivo (multipart) y se guardan en el disco `public`, carpeta `signatures/`, con nombre uuid. El servicio acepta jpg/png/webp hasta 5 MB y lanza `BadRequestError`; los FormRequest son más estrictos (`mimes:png,jpg,jpeg`, `max:2048` KB). En BD y en los Resources se guarda/devuelve la **ruta relativa**, no la URL.

**Documentos.** `POST /delivery_documents` recibe cabecera + `items[]` (`equipment_id`, `observations`) y `POST /return_documents` recibe cabecera + `items[]` (`delivery_document_detail_id`, `observations`); ambos crean documento y detalles dentro de `DB::transaction` y responden `data: true`. Las fechas las pone el servidor con `Carbon::now()` y `user_id` sale de `auth()->user()`. La devolución puede ser **parcial**: se devuelven sólo algunos detalles y la entrega queda en estado `parcial`. El estado (`pendiente`/`parcial`/`devuelto`) lo calcula `DeliveryDocument::status()` sobre sus detalles, no se guarda; `GET /delivery_documents` lo filtra con `?status=` (más `activo` = pendiente + parcial). Un equipo está asignado mientras exista un `DeliveryDocumentDetail` suyo sin `ReturnDocumentDetail`: eso es lo que miran los scopes `Equipment::available()`/`assigned()` y `DeliveryDocument::pendingDetails()`.

**Modelos.** Usan el atributo de Laravel 13 `#[Fillable([...])]` (y `#[Hidden]` en `User`), no las propiedades `$fillable`/`$hidden`. `User` implementa `JWTSubject` y mete `id/name/username/role` en los claims.

## Trampas conocidas

- El enum `EquipmentType` vive en `app/Enums/EquipmentType.php` (antes `EquipmentEnum.php`, que rompía el autoload PSR-4). `EquipmentRequest` lo valida con `Rule::enum()`. Incluye `phone` (RN-17).
- La tabla de `Equipment` es `equipments` y la columna del usuario que registra es `registerdBy` (así está en la migración y en el modelo).
- `DeliveryDocumentDetail::delivery_documents()` es un `belongsTo` con nombre en plural: **hay que pasarle la FK a mano** (`'delivery_document_id'`), si no Laravel deduce `delivery_documents_id` y revienta la consulta. Misma precaución al añadir relaciones con nombres en plural.
- `is_used` es columna real (`boolean` con `default(false)`), está en el `#[Fillable]` de `Equipment` y `EquipmentRequest` la exige. Estuvo comentada en la migración de creación mientras la BD sí la tenía `NOT NULL`, lo que rompía el `INSERT`; `2026_09_09_142613_add_is_used_to_equipments_table` la añade con guarda `Schema::hasColumn` a las BD que quedaron sin ella. La tabla trae `softDeletes()` y el modelo usa el trait `SoftDeletes` (RN-05), así que `Rule::exists` sobre `equipments` debe llevar `->whereNull('deleted_at')`.
- `app/Http/Requests/Department.php` es una copia perdida de la clase `App\Models\Department` (namespace que no corresponde a su ruta, no se autocarga). No la edites; el modelo bueno es `app/Models/Department.php`.
- `return_documents` no tiene columna de empleado ni de planta: `ReturnDocumentResource` los lee vía `delivery_document`.
- El spec OpenAPI se mantiene a mano en `resources/api-docs/openapi.yaml` y se sirve en `/api/documentation`; cubre las 27 rutas. Actualízalo al cambiar rutas, payloads o mensajes (valídalo con `Symfony\Component\Yaml\Yaml::parseFile`).
- `InitialUserSeeder` crea el admin desde `config('app.initial_admin')` (`ADMIN_*` en `.env`) y es idempotente: no pisa contraseñas ya cambiadas.
- Las reglas de negocio viven en los `after()` de los FormRequests (422) y en los controllers como `NotAcceptable` (406). Antes de tocar una validación de entrega/devolución lee `reglas.md`: cada regla dice qué valida y dónde. Ojo con el patrón que ya falló una vez: un método privado `validar*()` escrito pero **no listado en `after()`** no se ejecuta, y el código puesto después de un `return` dentro de un `catch` es inalcanzable.

## Docker y despliegue

`docker/php/Dockerfile` tiene dos targets finales: `runtime` (php-fpm, el que usa `docker-compose.yml` junto a nginx y el servicio `db`) y `standalone` (all-in-one con postgres y nginx dentro, publicado como `:latest`). Sólo el contenedor php-fpm migra y cachea; queue y scheduler esperan a `/run/app-ready`. Push a `main` dispara `.github/workflows/docker-publish.yml`, que autoincrementa el tag `v0.0.X` y publica ambas imágenes en Docker Hub.
