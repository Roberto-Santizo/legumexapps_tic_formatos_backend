<?php

use App\Models\Brand;
use App\Models\Caracteristic;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Equipment;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

/**
 * Token de un usuario con el rol indicado.
 */
function tokenDeReglas(string $role = 'admin'): string
{
    $factory = UserFactory::new();

    if ($role === 'admin') {
        $factory = $factory->admin();
    }

    $factory->create(['username' => "reglas-{$role}"]);

    return test()->postJson('/api/login', [
        'username' => "reglas-{$role}",
        'password' => 'password',
    ])->json('data.token');
}

/**
 * @return array<string, string>
 */
function headersDeReglas(string $role = 'admin'): array
{
    return ['Authorization' => 'Bearer '.tokenDeReglas($role)];
}

/**
 * Firma falsa. Cada llamada produce un archivo distinto para no chocar con RN-06.
 */
function firmaFalsa(string $nombre, int $ancho): UploadedFile
{
    return UploadedFile::fake()->image($nombre, $ancho, 60);
}

/**
 * Empleado, marca y equipos de varios tipos listos para entregar.
 *
 * @return array{employee: Employee, otro: Employee, laptop: Equipment, mouse: Equipment, teclado: Equipment, otraLaptop: Equipment, cable: Equipment, otroCable: Equipment}
 */
function escenarioDeReglas(): array
{
    $brand = Brand::create(['name' => 'Dell']);
    $department = Department::create(['name' => 'Tecnología de la Información']);
    $registeredBy = UserFactory::new()->create()->id;

    $equipo = function (string $name, string $type, string $serie) use ($brand, $registeredBy): Equipment {
        return Equipment::create([
            'name' => $name,
            'model' => 'Modelo '.$serie,
            'brand_id' => $brand->id,
            'serie' => $serie,
            'original' => true,
            'is_used' => false,
            'type' => $type,
            'registerdBy' => $registeredBy,
        ]);
    };

    return [
        'employee' => Employee::create(['code' => 'E-001', 'name' => 'Roberto Santizo', 'department_id' => $department->id]),
        'otro' => Employee::create(['code' => 'E-002', 'name' => 'Ana López', 'department_id' => $department->id]),
        'laptop' => $equipo('Laptop Latitude', 'laptop', 'S-LAP-1'),
        'otraLaptop' => $equipo('Laptop Vostro', 'laptop', 'S-LAP-2'),
        'mouse' => $equipo('Mouse MS116', 'mouse', 'S-MOU-1'),
        'teclado' => $equipo('Teclado KB216', 'keyboard', 'S-TEC-1'),
        'cable' => $equipo('Cable HDMI', 'cable', 'S-CAB-1'),
        'otroCable' => $equipo('Cable VGA', 'cable', 'S-CAB-2'),
    ];
}

/**
 * Registra una entrega y devuelve la respuesta.
 *
 * @param  array<int, array<string, mixed>>  $items
 * @param  array<string, string>  $headers
 */
function entregar(array $headers, int $employeeId, array $items, int $location = 1): TestResponse
{
    return test()->post('/api/delivery_documents', [
        'location' => $location,
        'employee_id' => $employeeId,
        'responsable_signature' => firmaFalsa('responsable.png', 120),
        'administrador_signature' => firmaFalsa('administrador.png', 160),
        'items' => $items,
    ], $headers);
}

/**
 * Registra una devolución y devuelve la respuesta.
 *
 * @param  array<int, array<string, mixed>>  $items
 * @param  array<string, string>  $headers
 */
function devolver(array $headers, int $deliveryDocumentId, array $items): TestResponse
{
    return test()->post('/api/return_documents', [
        'delivery_document_id' => $deliveryDocumentId,
        'responsable_signature' => firmaFalsa('responsable.png', 120),
        'administrador_signature' => firmaFalsa('administrador.png', 160),
        'items' => $items,
    ], $headers);
}

/**
 * Id del último documento de entrega registrado.
 *
 * @param  array<string, string>  $headers
 */
function ultimaEntrega(array $headers): int
{
    return test()->getJson('/api/delivery_documents', $headers)->json('data.0.id');
}

/**
 * Detalles pendientes de devolución de una entrega.
 *
 * @param  array<string, string>  $headers
 * @return array<int, array<string, mixed>>
 */
function pendientes(array $headers, int $deliveryDocumentId): array
{
    return test()->getJson("/api/delivery_documents/{$deliveryDocumentId}/pending_items", $headers)->json('data');
}

// ---------------------------------------------------------------------------
// Entrega
// ---------------------------------------------------------------------------

it('RN-01 no vuelve a entregar un equipo que no se ha devuelto', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'otro' => $otro, 'laptop' => $laptop] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])->assertStatus(201);

    entregar($headers, $otro->id, [['equipment_id' => $laptop->id]])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items.0.equipment_id' => 'El equipo ya está asignado en otra entrega y no se ha devuelto']);
});

it('RN-01 vuelve a entregar el equipo después de devolverlo', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'otro' => $otro, 'laptop' => $laptop] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])->assertStatus(201);

    $deliveryId = ultimaEntrega($headers);
    $detalle = pendientes($headers, $deliveryId)[0];

    devolver($headers, $deliveryId, [['delivery_document_detail_id' => $detalle['id']]])->assertStatus(201);

    entregar($headers, $otro->id, [['equipment_id' => $laptop->id]])->assertStatus(201);
});

it('RN-02 no entrega dos equipos del mismo tipo en la misma entrega', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop, 'otraLaptop' => $otraLaptop] = escenarioDeReglas();

    entregar($headers, $employee->id, [
        ['equipment_id' => $laptop->id],
        ['equipment_id' => $otraLaptop->id],
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items.1.equipment_id' => 'No se puede entregar más de un equipo de tipo laptop en la misma entrega']);
});

it('RN-02 no entrega un tipo que el empleado ya tiene vigente', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop, 'otraLaptop' => $otraLaptop] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])->assertStatus(201);

    entregar($headers, $employee->id, [['equipment_id' => $otraLaptop->id]])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items.0.equipment_id' => 'El empleado ya tiene asignado un equipo de tipo laptop']);
});

it('RN-02 permite dos equipos de un tipo sin tope', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'cable' => $cable, 'otroCable' => $otroCable] = escenarioDeReglas();

    entregar($headers, $employee->id, [
        ['equipment_id' => $cable->id],
        ['equipment_id' => $otroCable->id],
    ])->assertStatus(201);
});

it('RN-03 no repite el mismo equipo dentro de la misma entrega', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop] = escenarioDeReglas();

    entregar($headers, $employee->id, [
        ['equipment_id' => $laptop->id],
        ['equipment_id' => $laptop->id],
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items.0.equipment_id' => 'No se puede entregar el mismo equipo dos veces en el mismo documento']);
});

it('RN-04 rechaza una planta que no existe', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]], location: 99)
        ->assertStatus(422)
        ->assertJsonPath('errors.location.0', 'La planta seleccionada no es válida');
});

it('RN-04 muestra la planta con la etiqueta del enum', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]], location: 2)->assertStatus(201);

    test()->getJson('/api/delivery_documents', $headers)
        ->assertOk()
        ->assertJsonPath('data.0.location', 'Planta Parramos');
});

it('RN-05 no entrega un equipo dado de baja', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop] = escenarioDeReglas();

    $laptop->delete();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items.0.equipment_id' => 'El equipo seleccionado no existe o está dado de baja']);
});

it('RN-06 rechaza dos firmas idénticas', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop] = escenarioDeReglas();

    test()->post('/api/delivery_documents', [
        'location' => 1,
        'employee_id' => $employee->id,
        'responsable_signature' => firmaFalsa('responsable.png', 120),
        'administrador_signature' => firmaFalsa('administrador.png', 120),
        'items' => [['equipment_id' => $laptop->id]],
    ], $headers)
        ->assertStatus(422)
        ->assertJsonPath('errors.administrador_signature.0', 'La firma del administrador no puede ser la misma que la del responsable');
});

it('RN-07 sólo un admin registra entregas y devoluciones', function () {
    Storage::fake('public');
    $headers = headersDeReglas('user');
    ['employee' => $employee, 'laptop' => $laptop] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])
        ->assertStatus(403)
        ->assertJsonPath('message', 'No autorizado');

    devolver($headers, 1, [['delivery_document_detail_id' => 1]])->assertStatus(403);
});

it('RN-07 deja las lecturas abiertas a cualquier usuario autenticado', function () {
    $headers = headersDeReglas('user');

    test()->getJson('/api/delivery_documents', $headers)->assertOk();
    test()->getJson('/api/return_documents', $headers)->assertOk();
});

// ---------------------------------------------------------------------------
// Devolución
// ---------------------------------------------------------------------------

it('RN-08 no devuelve un equipo que no es de la entrega indicada', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'otro' => $otro, 'laptop' => $laptop, 'mouse' => $mouse] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])->assertStatus(201);
    $primera = ultimaEntrega($headers);

    entregar($headers, $otro->id, [['equipment_id' => $mouse->id]])->assertStatus(201);
    $segunda = ultimaEntrega($headers);

    $detalleDeLaSegunda = pendientes($headers, $segunda)[0];

    devolver($headers, $primera, [['delivery_document_detail_id' => $detalleDeLaSegunda['id']]])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items.0.delivery_document_detail_id' => 'El equipo no pertenece al documento de entrega indicado']);
});

it('RN-09 no repite el mismo detalle dentro de la misma devolución', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])->assertStatus(201);
    $deliveryId = ultimaEntrega($headers);
    $detalle = pendientes($headers, $deliveryId)[0];

    devolver($headers, $deliveryId, [
        ['delivery_document_detail_id' => $detalle['id']],
        ['delivery_document_detail_id' => $detalle['id']],
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items.0.delivery_document_detail_id' => 'No se puede devolver el mismo equipo dos veces en el mismo documento']);
});

it('RN-10 no devuelve dos veces el mismo equipo', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop, 'mouse' => $mouse] = escenarioDeReglas();

    entregar($headers, $employee->id, [
        ['equipment_id' => $laptop->id],
        ['equipment_id' => $mouse->id],
    ])->assertStatus(201);

    $deliveryId = ultimaEntrega($headers);
    $detalles = pendientes($headers, $deliveryId);

    // Devolución parcial: sólo el primer equipo.
    devolver($headers, $deliveryId, [['delivery_document_detail_id' => $detalles[0]['id']]])->assertStatus(201);

    // RN-10: el mismo detalle no se puede devolver otra vez.
    devolver($headers, $deliveryId, [['delivery_document_detail_id' => $detalles[0]['id']]])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items.0.delivery_document_detail_id' => 'El equipo ya fue devuelto en otro documento']);

    // Con la entrega ya cerrada, RN-10 sigue bloqueando cada detalle: el 406 de
    // RN-11 en el controller queda como última defensa (por ejemplo, ante dos
    // peticiones simultáneas), no como la respuesta habitual.
    devolver($headers, $deliveryId, [['delivery_document_detail_id' => $detalles[1]['id']]])->assertStatus(201);

    devolver($headers, $deliveryId, [['delivery_document_detail_id' => $detalles[1]['id']]])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['items.0.delivery_document_detail_id' => 'El equipo ya fue devuelto en otro documento']);
});

it('RN-13 no agrega a una devolución un detalle de otra entrega', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'otro' => $otro, 'laptop' => $laptop, 'mouse' => $mouse] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])->assertStatus(201);
    $primera = ultimaEntrega($headers);
    $detalleDeLaPrimera = pendientes($headers, $primera)[0];

    entregar($headers, $otro->id, [['equipment_id' => $mouse->id]])->assertStatus(201);
    $segunda = ultimaEntrega($headers);
    $detalleDeLaSegunda = pendientes($headers, $segunda)[0];

    devolver($headers, $primera, [['delivery_document_detail_id' => $detalleDeLaPrimera['id']]])->assertStatus(201);

    $returnDocumentId = test()->getJson('/api/return_documents', $headers)->json('data.0.id');

    test()->postJson('/api/return_document_details', [
        'return_document_id' => $returnDocumentId,
        'delivery_document_detail_id' => $detalleDeLaSegunda['id'],
    ], $headers)
        ->assertStatus(422)
        ->assertJsonPath('errors.delivery_document_detail_id.0', 'El equipo no pertenece al documento de entrega de esta devolución');
});

// ---------------------------------------------------------------------------
// Edición y borrado de documentos
// ---------------------------------------------------------------------------

it('RN-14 no elimina una entrega con devoluciones registradas', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop, 'mouse' => $mouse] = escenarioDeReglas();

    entregar($headers, $employee->id, [
        ['equipment_id' => $laptop->id],
        ['equipment_id' => $mouse->id],
    ])->assertStatus(201);

    $deliveryId = ultimaEntrega($headers);
    $detalles = pendientes($headers, $deliveryId);

    devolver($headers, $deliveryId, [['delivery_document_detail_id' => $detalles[0]['id']]])->assertStatus(201);

    test()->deleteJson("/api/delivery_documents/{$deliveryId}", [], $headers)
        ->assertStatus(406)
        ->assertJsonPath('message', 'No se puede eliminar una entrega que ya tiene devoluciones registradas');
});

it('RN-15 no quita de la entrega un equipo ya devuelto', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop, 'mouse' => $mouse] = escenarioDeReglas();

    entregar($headers, $employee->id, [
        ['equipment_id' => $laptop->id],
        ['equipment_id' => $mouse->id],
    ])->assertStatus(201);

    $deliveryId = ultimaEntrega($headers);
    $detalles = pendientes($headers, $deliveryId);

    devolver($headers, $deliveryId, [['delivery_document_detail_id' => $detalles[0]['id']]])->assertStatus(201);

    test()->deleteJson("/api/delivery_document_details/{$detalles[0]['id']}", [], $headers)
        ->assertStatus(406)
        ->assertJsonPath('message', 'No se puede quitar de la entrega un equipo que ya fue devuelto');

    test()->deleteJson("/api/delivery_document_details/{$detalles[1]['id']}", [], $headers)->assertOk();
});

it('RN-16 no agrega equipos a una entrega ya devuelta por completo', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop, 'mouse' => $mouse] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])->assertStatus(201);
    $deliveryId = ultimaEntrega($headers);
    $detalle = pendientes($headers, $deliveryId)[0];

    devolver($headers, $deliveryId, [['delivery_document_detail_id' => $detalle['id']]])->assertStatus(201);

    test()->postJson('/api/delivery_document_details', [
        'delivery_document_id' => $deliveryId,
        'equipment_id' => $mouse->id,
    ], $headers)
        ->assertStatus(422)
        ->assertJsonPath('errors.delivery_document_id.0', 'No se pueden agregar equipos a una entrega que ya fue devuelta por completo');
});

it('RN-01 y RN-02 también aplican al agregar un equipo suelto a la entrega', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'otro' => $otro, 'laptop' => $laptop, 'otraLaptop' => $otraLaptop, 'mouse' => $mouse] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])->assertStatus(201);
    $primera = ultimaEntrega($headers);

    entregar($headers, $otro->id, [['equipment_id' => $mouse->id]])->assertStatus(201);
    $segunda = ultimaEntrega($headers);

    // RN-01: la laptop sigue asignada a otro empleado.
    test()->postJson('/api/delivery_document_details', [
        'delivery_document_id' => $segunda,
        'equipment_id' => $laptop->id,
    ], $headers)
        ->assertStatus(422)
        ->assertJsonPath('errors.equipment_id.0', 'El equipo ya está asignado en otra entrega y no se ha devuelto');

    // RN-02: el empleado de la primera entrega ya tiene una laptop.
    test()->postJson('/api/delivery_document_details', [
        'delivery_document_id' => $primera,
        'equipment_id' => $otraLaptop->id,
    ], $headers)
        ->assertStatus(422)
        ->assertJsonPath('errors.equipment_id.0', 'El empleado ya tiene asignado un equipo de tipo laptop');
});

// ---------------------------------------------------------------------------
// Catálogo
// ---------------------------------------------------------------------------

it('RN-17 sólo acepta características en los tipos que las admiten', function () {
    $headers = headersDeReglas();
    ['laptop' => $laptop, 'mouse' => $mouse] = escenarioDeReglas();

    test()->postJson('/api/caracteristics', [
        'name' => 'RAM',
        'description' => '16 GB',
        'equipment_id' => $laptop->id,
    ], $headers)->assertStatus(201);

    test()->postJson('/api/caracteristics', [
        'name' => 'RAM',
        'description' => '16 GB',
        'equipment_id' => $mouse->id,
    ], $headers)
        ->assertStatus(422)
        ->assertJsonPath('errors.equipment_id.0', 'El tipo de equipo seleccionado no admite características');
});

it('RN-18 no repite el nombre de característica en el mismo equipo', function () {
    $headers = headersDeReglas();
    ['laptop' => $laptop, 'otraLaptop' => $otraLaptop] = escenarioDeReglas();

    Caracteristic::create(['name' => 'RAM', 'description' => '16 GB', 'equipment_id' => $laptop->id]);

    test()->postJson('/api/caracteristics', [
        'name' => 'RAM',
        'description' => '32 GB',
        'equipment_id' => $laptop->id,
    ], $headers)
        ->assertStatus(422)
        ->assertJsonPath('errors.name.0', 'El equipo ya tiene una característica con ese nombre');

    // El mismo nombre en otro equipo sí se acepta.
    test()->postJson('/api/caracteristics', [
        'name' => 'RAM',
        'description' => '32 GB',
        'equipment_id' => $otraLaptop->id,
    ], $headers)->assertStatus(201);
});

it('RN-19 no repite la serie de un equipo', function () {
    $headers = headersDeReglas();
    ['laptop' => $laptop] = escenarioDeReglas();

    test()->postJson('/api/equipments', [
        'name' => 'Otra laptop',
        'model' => 'Otro modelo',
        'brand_id' => $laptop->brand_id,
        'serie' => $laptop->serie,
        'original' => true,
        'is_used' => false,
        'type' => 'laptop',
    ], $headers)
        ->assertStatus(422)
        ->assertJsonPath('errors.serie.0', 'Ya existe un equipo registrado con esa serie');
});

it('RN-20 no repite el nombre de marca ni de departamento', function () {
    $headers = headersDeReglas();
    escenarioDeReglas();

    test()->postJson('/api/brands', ['name' => 'Dell'], $headers)
        ->assertStatus(422)
        ->assertJsonPath('errors.name.0', 'Ya existe una marca con ese nombre');

    test()->postJson('/api/departments', ['name' => 'Tecnología de la Información'], $headers)
        ->assertStatus(422)
        ->assertJsonPath('errors.name.0', 'Ya existe un departamento con ese nombre');
});

it('RN-21 no repite el código de empleado', function () {
    $headers = headersDeReglas();
    ['employee' => $employee] = escenarioDeReglas();

    test()->postJson('/api/employees', [
        'name' => 'Otro empleado',
        'code' => $employee->code,
        'department_id' => $employee->department_id,
    ], $headers)
        ->assertStatus(422)
        ->assertJsonPath('errors.code.0', 'Ya existe un empleado con ese código');
});

it('RN-22 no cambia la serie ni el tipo de un equipo asignado', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])->assertStatus(201);

    $payload = [
        'name' => $laptop->name,
        'model' => $laptop->model,
        'brand_id' => $laptop->brand_id,
        'original' => true,
        'is_used' => false,
        'type' => $laptop->type,
        'serie' => 'SERIE-NUEVA',
    ];

    test()->putJson("/api/equipments/{$laptop->id}", $payload, $headers)
        ->assertStatus(406)
        ->assertJsonPath('message', 'No se puede cambiar la serie ni el tipo de un equipo que está asignado');

    // Cambiar sólo el nombre sí se permite y se persiste.
    test()->putJson("/api/equipments/{$laptop->id}", [...$payload, 'serie' => $laptop->serie, 'name' => 'Laptop renombrada'], $headers)
        ->assertOk();

    expect($laptop->fresh()->name)->toBe('Laptop renombrada');
});

it('RN-23 no da de baja un equipo asignado', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'laptop' => $laptop, 'mouse' => $mouse] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])->assertStatus(201);

    test()->deleteJson("/api/equipments/{$laptop->id}", [], $headers)
        ->assertStatus(406)
        ->assertJsonPath('message', 'No se puede dar de baja un equipo que está asignado');

    test()->deleteJson("/api/equipments/{$mouse->id}", [], $headers)->assertOk();

    expect(Equipment::find($mouse->id))->toBeNull();
});

it('RN-24 no elimina un empleado con equipo asignado', function () {
    Storage::fake('public');
    $headers = headersDeReglas();
    ['employee' => $employee, 'otro' => $otro, 'laptop' => $laptop] = escenarioDeReglas();

    entregar($headers, $employee->id, [['equipment_id' => $laptop->id]])->assertStatus(201);

    test()->deleteJson("/api/employees/{$employee->id}", [], $headers)
        ->assertStatus(406)
        ->assertJsonPath('message', 'No se puede eliminar un empleado que tiene equipo asignado');

    test()->deleteJson("/api/employees/{$otro->id}", [], $headers)->assertOk();

    expect(Employee::find($otro->id))->toBeNull();
});
