<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Documentación de la API (Swagger UI)
|--------------------------------------------------------------------------
|
| El spec OpenAPI vive en resources/api-docs/openapi.yaml, fuera de los
| controllers. Estas rutas sólo lo sirven y lo renderizan con Swagger UI.
|
*/

Route::prefix('api/documentation')->group(function () {
    Route::get('/', fn () => view('api-docs', ['specUrl' => route('api-docs.spec')]))
        ->name('api-docs.ui');

    Route::get('/openapi.yaml', function () {
        $spec = resource_path('api-docs/openapi.yaml');

        abort_unless(file_exists($spec), 404, 'No se encontró el archivo openapi.yaml.');

        // Sin no-store el navegador aplica caché heurística (response()->file()
        // manda `Cache-Control: public` sin max-age) y Swagger UI sigue
        // mostrando un spec viejo tras editar el yaml.
        return response()->file($spec, [
            'Content-Type' => 'application/yaml; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    })->name('api-docs.spec');
});
