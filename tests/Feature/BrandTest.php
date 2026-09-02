<?php

use App\Models\Brand;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function tokenDeMarcas(): string
{
    UserFactory::new()->create(['username' => 'marcas']);

    return test()->postJson('/api/login', [
        'username' => 'marcas',
        'password' => 'password',
    ])->json('data.token');
}

it('rechaza con 401 las rutas de marcas sin token', function (string $method, string $uri) {
    $this->json($method, $uri, ['name' => 'Legumex'])->assertStatus(401);
})->with([
    ['GET', '/api/brands'],
    ['POST', '/api/brands'],
    ['GET', '/api/brands/1'],
    ['PUT', '/api/brands/1'],
]);

it('lista las marcas con un token válido', function () {
    $headers = ['Authorization' => 'Bearer '.tokenDeMarcas()];

    Brand::create(['name' => 'Legumex']);

    $this->getJson('/api/brands', $headers)
        ->assertOk()
        ->assertJsonPath('statusCode', 200)
        ->assertJsonPath('message', 'Marcas Obtenidas Correctamente')
        ->assertJsonPath('data.0.name', 'Legumex');
});

it('crea una marca y devuelve el registro completo', function () {
    $headers = ['Authorization' => 'Bearer '.tokenDeMarcas()];

    $this->postJson('/api/brands', ['name' => 'Legumex'], $headers)
        ->assertStatus(201)
        ->assertJsonPath('statusCode', 201)
        ->assertJsonPath('message', 'Marca Creada Correctamente')
        ->assertJsonPath('data.name', 'Legumex')
        ->assertJsonStructure(['statusCode', 'message', 'data' => ['id', 'name', 'created_at', 'updated_at']]);

    $this->assertDatabaseHas('brands', ['name' => 'Legumex']);
});

it('obtiene y actualiza una marca con un token válido', function () {
    $headers = ['Authorization' => 'Bearer '.tokenDeMarcas()];

    $brand = Brand::create(['name' => 'Legumex']);

    $this->getJson("/api/brands/{$brand->id}", $headers)
        ->assertOk()
        ->assertJsonPath('message', 'Marca Obtenida Correctamente')
        ->assertJsonPath('data.id', $brand->id);

    $this->putJson("/api/brands/{$brand->id}", ['name' => 'Legumex Premium'], $headers)
        ->assertOk()
        ->assertJsonPath('message', 'Marca Actualizada Correctamente')
        ->assertJsonPath('data.name', 'Legumex Premium');

    $this->assertDatabaseHas('brands', ['id' => $brand->id, 'name' => 'Legumex Premium']);
});

it('devuelve 422 cuando falta name', function (string $method, string $uri) {
    $headers = ['Authorization' => 'Bearer '.tokenDeMarcas()];

    Brand::create(['name' => 'Legumex']);

    $this->json($method, $uri, [], $headers)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name'])
        ->assertJsonPath('errors.name.0', 'El campo de nombre es obligatorio.');
})->with([
    ['POST', '/api/brands'],
    ['PUT', '/api/brands/1'],
]);

it('devuelve 404 cuando la marca no existe', function (string $method, string $uri) {
    $headers = ['Authorization' => 'Bearer '.tokenDeMarcas()];

    $this->json($method, $uri, ['name' => 'Legumex'], $headers)
        ->assertStatus(404)
        ->assertExactJson([
            'statusCode' => 404,
            'message' => 'Marca no encontrada',
            'data' => null,
        ]);
})->with([
    ['GET', '/api/brands/999'],
    ['PUT', '/api/brands/999'],
]);
