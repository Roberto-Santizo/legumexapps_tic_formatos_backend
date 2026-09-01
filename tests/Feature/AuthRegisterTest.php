<?php

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function tokenPara(User $user): string
{
    return test()->postJson('/api/login', [
        'username' => $user->username,
        'password' => 'password',
    ])->json('data.token');
}

it('permite a un admin crear un usuario', function () {
    $admin = UserFactory::new()->admin()->create(['username' => 'admin']);

    $response = $this->postJson('/api/register', [
        'name' => 'Roberto Santizo',
        'username' => 'rsantizo',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role' => 'user',
    ], ['Authorization' => 'Bearer '.tokenPara($admin)]);

    $response->assertStatus(201)
        ->assertJsonPath('statusCode', 201)
        ->assertJsonPath('message', 'Usuario Creado Correctamente')
        ->assertJsonPath('data.name', 'Roberto Santizo')
        ->assertJsonPath('data.username', 'rsantizo')
        ->assertJsonPath('data.role', 'user')
        ->assertJsonStructure(['statusCode', 'message', 'data' => ['id', 'name', 'username', 'role']]);

    $creado = User::where('username', 'rsantizo')->first();

    expect($creado)->not->toBeNull()
        ->and(Hash::check('secret123', $creado->password))->toBeTrue();
});

it('no expone la contraseña en la respuesta', function () {
    $admin = UserFactory::new()->admin()->create(['username' => 'admin']);

    $this->postJson('/api/register', [
        'name' => 'Roberto Santizo',
        'username' => 'rsantizo',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role' => 'user',
    ], ['Authorization' => 'Bearer '.tokenPara($admin)])
        ->assertStatus(201)
        ->assertJsonMissingPath('data.password');
});

it('rechaza el registro sin token', function () {
    $this->postJson('/api/register', [
        'name' => 'Roberto Santizo',
        'username' => 'rsantizo',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role' => 'user',
    ])->assertStatus(401);

    expect(User::where('username', 'rsantizo')->exists())->toBeFalse();
});

it('bloquea con 403 a un usuario que no es admin', function () {
    $basico = UserFactory::new()->create(['username' => 'basico']);

    $this->postJson('/api/register', [
        'name' => 'Roberto Santizo',
        'username' => 'rsantizo',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role' => 'user',
    ], ['Authorization' => 'Bearer '.tokenPara($basico)])
        ->assertStatus(403)
        ->assertExactJson([
            'statusCode' => 403,
            'message' => 'No autorizado',
            'data' => null,
        ]);

    expect(User::where('username', 'rsantizo')->exists())->toBeFalse();
});

it('bloquea con 403 a un adminagricola', function () {
    $agricola = UserFactory::new()->create(['username' => 'agricola', 'role' => 'adminagricola']);

    $this->postJson('/api/register', [
        'name' => 'Roberto Santizo',
        'username' => 'rsantizo',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role' => 'user',
    ], ['Authorization' => 'Bearer '.tokenPara($agricola)])
        ->assertStatus(403);
});

it('rechaza un username duplicado', function () {
    $admin = UserFactory::new()->admin()->create(['username' => 'admin']);
    UserFactory::new()->create(['username' => 'rsantizo']);

    $this->postJson('/api/register', [
        'name' => 'Roberto Santizo',
        'username' => 'rsantizo',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role' => 'user',
    ], ['Authorization' => 'Bearer '.tokenPara($admin)])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['username'])
        ->assertJsonPath('errors.username.0', 'El usuario ya se encuentra registrado.');
});

it('rechaza un rol no permitido', function () {
    $admin = UserFactory::new()->admin()->create(['username' => 'admin']);

    $this->postJson('/api/register', [
        'name' => 'Roberto Santizo',
        'username' => 'rsantizo',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'role' => 'superuser',
    ], ['Authorization' => 'Bearer '.tokenPara($admin)])
        ->assertStatus(422)
        ->assertJsonPath('errors.role.0', 'El rol seleccionado no es válido.');
});

it('exige la confirmación de la contraseña', function () {
    $admin = UserFactory::new()->admin()->create(['username' => 'admin']);

    $this->postJson('/api/register', [
        'name' => 'Roberto Santizo',
        'username' => 'rsantizo',
        'password' => 'secret123',
        'password_confirmation' => 'otra-cosa',
        'role' => 'user',
    ], ['Authorization' => 'Bearer '.tokenPara($admin)])
        ->assertStatus(422)
        ->assertJsonPath('errors.password.0', 'La confirmación de contraseña no coincide.');
});

it('exige los campos obligatorios', function () {
    $admin = UserFactory::new()->admin()->create(['username' => 'admin']);

    $this->postJson('/api/register', [], ['Authorization' => 'Bearer '.tokenPara($admin)])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'username', 'password', 'role']);
});
