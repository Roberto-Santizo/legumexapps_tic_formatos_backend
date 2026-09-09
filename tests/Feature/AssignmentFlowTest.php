<?php

use App\Models\Brand;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Equipment;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * Token de un usuario recién creado para las rutas de asignación.
 */
function tokenDeAsignaciones(): string
{
    UserFactory::new()->admin()->create(['username' => 'asignaciones']);

    return test()->postJson('/api/login', [
        'username' => 'asignaciones',
        'password' => 'password',
    ])->json('data.token');
}

/**
 * Empleado y dos equipos listos para entregar.
 *
 * @return array{employee: Employee, laptop: Equipment, mouse: Equipment}
 */
function escenarioDeAsignacion(): array
{
    $brand = Brand::create(['name' => 'Dell']);
    $department = Department::create(['name' => 'Tecnología de la Información']);
    $employee = Employee::create(['code' => 'E-001', 'name' => 'Roberto Santizo', 'department_id' => $department->id]);
    $registeredBy = UserFactory::new()->create()->id;

    return [
        'employee' => $employee,
        'laptop' => Equipment::create([
            'name' => 'Laptop Dell Latitude',
            'model' => 'Latitude 5440',
            'brand_id' => $brand->id,
            'serie' => '5CD1234XYZ',
            'original' => true,
            'type' => 'laptop',
            'registerdBy' => $registeredBy,
        ]),
        'mouse' => Equipment::create([
            'name' => 'Mouse Dell',
            'model' => 'MS116',
            'brand_id' => $brand->id,
            'serie' => 'MS1234XYZ',
            'original' => true,
            'type' => 'mouse',
            'registerdBy' => $registeredBy,
        ]),
    ];
}

it('recorre el flujo completo de entrega y devolución parcial', function () {
    Storage::fake('public');

    $headers = ['Authorization' => 'Bearer '.tokenDeAsignaciones()];
    ['employee' => $employee, 'laptop' => $laptop, 'mouse' => $mouse] = escenarioDeAsignacion();

    // Antes de entregar, ambos equipos están disponibles.
    $this->getJson('/api/equipments/available', $headers)
        ->assertOk()
        ->assertJsonCount(2, 'data');

    // 1. Entrega de los dos equipos.
    $this->post('/api/delivery_documents', [
        'location' => 1,
        'employee_id' => $employee->id,
        'observations' => 'Entrega inicial de equipo',
        'responsable_signature' => UploadedFile::fake()->image('responsable.png'),
        'administrador_signature' => UploadedFile::fake()->image('administrador.png'),
        'items' => [
            ['equipment_id' => $laptop->id, 'observations' => 'Se entrega sin cargador'],
            ['equipment_id' => $mouse->id],
        ],
    ], $headers)
        ->assertStatus(201)
        ->assertJsonPath('message', 'Documento de Entregas Creado Correctamente');

    $deliveryDocumentId = $this->getJson('/api/delivery_documents', $headers)
        ->assertOk()
        ->assertJsonPath('data.0.status', 'pendiente')
        ->assertJsonPath('data.0.observations', 'Entrega inicial de equipo')
        ->json('data.0.id');

    // 2. Con la entrega registrada ya no hay equipos disponibles.
    $this->getJson('/api/equipments/available', $headers)
        ->assertOk()
        ->assertJsonCount(0, 'data');

    // 3. Los dos equipos están pendientes de devolución.
    $pendingItems = $this->getJson("/api/delivery_documents/{$deliveryDocumentId}/pending_items", $headers)
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->json('data');

    $mouseDetail = collect($pendingItems)->firstWhere('equipment_id', $mouse->id);

    // 4. Devolución parcial: sólo regresa el mouse.
    $this->post('/api/return_documents', [
        'delivery_document_id' => $deliveryDocumentId,
        'observations' => 'Devuelve únicamente el mouse',
        'responsable_signature' => UploadedFile::fake()->image('responsable.png'),
        'administrador_signature' => UploadedFile::fake()->image('administrador.png'),
        'items' => [
            ['delivery_document_detail_id' => $mouseDetail['id'], 'observations' => 'En buen estado'],
        ],
    ], $headers)
        ->assertStatus(201)
        ->assertJsonPath('message', 'Devolución de Documento Creado Correctamente');

    // 5. La entrega queda parcial y sólo la laptop sigue pendiente.
    $this->getJson("/api/delivery_documents/{$deliveryDocumentId}", $headers)
        ->assertOk()
        ->assertJsonPath('data.status', 'parcial')
        ->assertJsonPath('data.pending_items_count', 1);

    $this->getJson("/api/delivery_documents/{$deliveryDocumentId}/pending_items", $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.equipment_id', $laptop->id);

    // 6. El empleado conserva sólo la laptop y el mouse vuelve a estar disponible.
    $this->getJson("/api/employees/{$employee->id}/equipments", $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.equipment_id', $laptop->id);

    $this->getJson('/api/equipments/available', $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $mouse->id);

    // 7. El historial del mouse muestra la entrega y su devolución.
    $this->getJson("/api/equipments/{$mouse->id}/history", $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.returned', true)
        ->assertJsonPath('data.0.employee_name', 'Roberto Santizo')
        ->assertJsonPath('data.0.return_observations', 'En buen estado');
});

it('filtra los documentos de entrega por empleado y por estado', function () {
    Storage::fake('public');

    $headers = ['Authorization' => 'Bearer '.tokenDeAsignaciones()];
    ['employee' => $employee, 'laptop' => $laptop] = escenarioDeAsignacion();

    $this->post('/api/delivery_documents', [
        'location' => 1,
        'employee_id' => $employee->id,
        'responsable_signature' => UploadedFile::fake()->image('responsable.png'),
        'administrador_signature' => UploadedFile::fake()->image('administrador.png'),
        'items' => [['equipment_id' => $laptop->id]],
    ], $headers)->assertStatus(201);

    $this->getJson("/api/delivery_documents?employeeId={$employee->id}&status=activo", $headers)
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->getJson('/api/delivery_documents?status=devuelto', $headers)
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->getJson('/api/delivery_documents?status=inventado', $headers)
        ->assertStatus(422)
        ->assertJsonPath('message', 'El estado debe ser pendiente, parcial, devuelto o activo');
});

it('rechaza sin token las rutas nuevas de asignación', function (string $method, string $uri) {
    $this->json($method, $uri)->assertStatus(401);
})->with([
    ['GET', '/api/equipments/available'],
    ['GET', '/api/equipments/1/history'],
    ['GET', '/api/employees/1/equipments'],
    ['GET', '/api/delivery_documents/1/pending_items'],
]);
