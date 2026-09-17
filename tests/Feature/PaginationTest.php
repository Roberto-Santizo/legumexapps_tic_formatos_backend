<?php

use App\Models\Brand;
use App\Models\Department;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function tokenDePaginacion(): array
{
    UserFactory::new()->create(['username' => 'paginacion']);

    $token = test()->postJson('/api/login', [
        'username' => 'paginacion',
        'password' => 'password',
    ])->json('data.token');

    return ['Authorization' => 'Bearer '.$token];
}

it('devuelve todos los registros cuando no se envía limit', function () {
    $headers = tokenDePaginacion();

    foreach (range(1, 20) as $i) {
        Brand::create(['name' => "Marca {$i}"]);
    }

    $this->getJson('/api/brands', $headers)
        ->assertOk()
        ->assertJsonCount(20, 'data')
        ->assertJsonMissingPath('total')
        ->assertJsonMissingPath('currentPage')
        ->assertJsonMissingPath('lastPage');
});

it('pagina con el tamaño indicado en limit', function () {
    $headers = tokenDePaginacion();

    foreach (range(1, 7) as $i) {
        Department::create(['name' => "Departamento {$i}"]);
    }

    $this->getJson('/api/departments?limit=3', $headers)
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.name', 'Departamento 1')
        ->assertJsonPath('total', 7)
        ->assertJsonPath('currentPage', 1)
        ->assertJsonPath('lastPage', 3);

    $this->getJson('/api/departments?limit=3&page=3', $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Departamento 7')
        ->assertJsonPath('currentPage', 3);
});

it('rechaza un limit que no sea un entero positivo', function (string $query, string $message) {
    $headers = tokenDePaginacion();

    $this->getJson("/api/brands?{$query}", $headers)
        ->assertStatus(422)
        ->assertJsonPath('message', $message);
})->with([
    ['limit=abc', 'El límite debe ser un valor numérico'],
    ['limit=0', 'El límite debe ser mayor a cero'],
    ['limit=5&page=0', 'La página debe ser mayor a cero'],
]);

it('acepta limit en todos los listados paginables', function (string $uri) {
    $headers = tokenDePaginacion();

    $this->getJson("{$uri}?limit=5", $headers)
        ->assertOk()
        ->assertJsonPath('total', 0)
        ->assertJsonPath('currentPage', 1);

    $this->getJson($uri, $headers)
        ->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonMissingPath('total');
})->with([
    '/api/brands',
    '/api/departments',
    '/api/employees',
    '/api/equipments',
    '/api/caracteristics',
    '/api/delivery_documents',
    '/api/delivery_document_details',
    '/api/return_documents',
    '/api/return_document_details',
]);
