<?php

use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

it('inicia sesión con credenciales válidas', function () {
    UserFactory::new()->admin()->create(['name' => 'Roberto', 'username' => 'roberto']);

    $response = $this->postJson('/api/login', [
        'username' => 'roberto',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('statusCode', 200)
        ->assertJsonPath('message', 'Sesión Iniciada Correctamente')
        ->assertJsonPath('data.name', 'Roberto')
        ->assertJsonPath('data.role', 'admin')
        ->assertJsonStructure(['statusCode', 'message', 'data' => ['name', 'role', 'token']]);

    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
});

it('rechaza credenciales inválidas con 400 y el envelope de error', function () {
    UserFactory::new()->create(['username' => 'roberto']);

    $this->postJson('/api/login', [
        'username' => 'roberto',
        'password' => 'incorrecta',
    ])->assertStatus(400)
        ->assertExactJson([
            'statusCode' => 400,
            'message' => 'Credenciales inválidas',
            'data' => null,
        ]);
});

it('valida que username sea obligatorio', function () {
    $this->postJson('/api/login', ['password' => 'password'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['username'])
        ->assertJsonPath('errors.username.0', 'El campo de usuario es obligatorio.');
});

it('rechaza check-status sin token', function () {
    $this->getJson('/api/check-status')->assertStatus(401);
});

it('devuelve el usuario y un token nuevo en check-status', function () {
    UserFactory::new()->admin()->create(['name' => 'Roberto', 'username' => 'roberto']);

    $token = $this->postJson('/api/login', [
        'username' => 'roberto',
        'password' => 'password',
    ])->json('data.token');

    $this->getJson('/api/check-status', ['Authorization' => "Bearer {$token}"])
        ->assertOk()
        ->assertJsonPath('statusCode', 200)
        ->assertJsonPath('message', 'Usuario Obtenido Correctamente')
        ->assertJsonPath('data.name', 'Roberto')
        ->assertJsonPath('data.role', 'admin')
        ->assertJsonStructure(['statusCode', 'message', 'data' => ['name', 'role', 'token']]);
});

it('bloquea con 403 y el envelope de error a quien no es admin', function () {
    Route::middleware(['api', 'jwt.auth', 'admin'])->get('/api/solo-admin', fn () => response()->json(['ok' => true]));

    UserFactory::new()->create(['username' => 'basico']);

    $token = $this->postJson('/api/login', [
        'username' => 'basico',
        'password' => 'password',
    ])->json('data.token');

    $this->getJson('/api/solo-admin', ['Authorization' => "Bearer {$token}"])
        ->assertStatus(403)
        ->assertExactJson([
            'statusCode' => 403,
            'message' => 'No autorizado',
            'data' => null,
        ]);
});
