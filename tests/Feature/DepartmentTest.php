<?php

use App\Models\Department;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function tokenDeDepartamentos(): string
{
    UserFactory::new()->create(['username' => 'departamentos']);

    return test()->postJson('/api/login', [
        'username' => 'departamentos',
        'password' => 'password',
    ])->json('data.token');
}

it('rechaza con 401 las rutas de departamentos sin token', function (string $method, string $uri) {
    $this->json($method, $uri, ['name' => 'Tecnología'])->assertStatus(401);
})->with([
    ['GET', '/api/departments'],
    ['POST', '/api/departments'],
    ['GET', '/api/departments/1'],
    ['PUT', '/api/departments/1'],
]);

it('lista los departamentos con un token válido', function () {
    $headers = ['Authorization' => 'Bearer '.tokenDeDepartamentos()];

    Department::create(['name' => 'Tecnología de la Información']);

    $this->getJson('/api/departments', $headers)
        ->assertOk()
        ->assertJsonPath('statusCode', 200)
        ->assertJsonPath('message', 'Departamentos Obtenidos Correctamente')
        ->assertJsonPath('data.0.name', 'Tecnología de la Información');
});

it('crea un departamento y devuelve el registro completo', function () {
    $headers = ['Authorization' => 'Bearer '.tokenDeDepartamentos()];

    $this->postJson('/api/departments', ['name' => 'Tecnología de la Información'], $headers)
        ->assertStatus(201)
        ->assertJsonPath('statusCode', 201)
        ->assertJsonPath('message', 'Departamento Creado Correctamente')
        ->assertJsonPath('data.name', 'Tecnología de la Información')
        ->assertJsonStructure(['statusCode', 'message', 'data' => ['id', 'name', 'created_at', 'updated_at']]);

    $this->assertDatabaseHas('departments', ['name' => 'Tecnología de la Información']);
});

it('obtiene y actualiza un departamento con un token válido', function () {
    $headers = ['Authorization' => 'Bearer '.tokenDeDepartamentos()];

    $department = Department::create(['name' => 'Tecnología de la Información']);

    $this->getJson("/api/departments/{$department->id}", $headers)
        ->assertOk()
        ->assertJsonPath('message', 'Departamento Obtenido Correctamente')
        ->assertJsonPath('data.id', $department->id);

    $this->putJson("/api/departments/{$department->id}", ['name' => 'Tecnología'], $headers)
        ->assertOk()
        ->assertJsonPath('message', 'Departamento Actualizado Correctamente')
        ->assertJsonPath('data.name', 'Tecnología');

    $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'Tecnología']);
});

it('devuelve 422 cuando falta name', function (string $method, string $uri) {
    $headers = ['Authorization' => 'Bearer '.tokenDeDepartamentos()];

    Department::create(['name' => 'Tecnología de la Información']);

    $this->json($method, $uri, [], $headers)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name'])
        ->assertJsonPath('errors.name.0', 'El campo de nombre es obligatorio.');
})->with([
    ['POST', '/api/departments'],
    ['PUT', '/api/departments/1'],
]);

it('devuelve 404 cuando el departamento no existe', function (string $method, string $uri) {
    $headers = ['Authorization' => 'Bearer '.tokenDeDepartamentos()];

    $this->json($method, $uri, ['name' => 'Tecnología'], $headers)
        ->assertStatus(404)
        ->assertExactJson([
            'statusCode' => 404,
            'message' => 'Departamento no encontrado',
            'data' => null,
        ]);
})->with([
    ['GET', '/api/departments/999'],
    ['PUT', '/api/departments/999'],
]);
