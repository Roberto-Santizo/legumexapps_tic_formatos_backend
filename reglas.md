# Reglas de negocio — Asignación de equipos TIC

Este documento describe las reglas que debe cumplir el proceso de entrega y
devolución de equipo, con el código de cada una. **Todas están implementadas y
cubiertas por `tests/Feature/ReglasNegocioTest.php`** (ver [`flujo.md`](flujo.md)
para el flujo de los endpoints).

Los bloques de código de abajo son la referencia de *qué* valida cada regla y
*dónde* vive; el código real puede diferir en detalles (ver las notas de
implementación al final de cada regla que cambió).

---

## 0. Convenciones antes de escribir código

### 0.1 Dónde va cada validación

| Tipo de regla | Dónde | Respuesta |
|---|---|---|
| Depende sólo del payload (formato, duplicados dentro de la misma petición) | `rules()` del FormRequest | `422` con `errors` |
| Depende de la base pero es "el dato enviado no sirve" | `after()` del FormRequest | `422` con `errors` |
| Depende del **estado** del documento (ya devuelto, ya cerrado) | Controller, lanzando `NotAcceptable` | `406` con `{statusCode, message, data: null}` |
| Integridad que nunca debe romperse | Índice/constraint en migración | `500` (última línea de defensa) |

Regla práctica: si el usuario puede corregir el formulario y reintentar → `422`.
Si el problema es que la operación no aplica sobre ese documento → `406`.

### 0.2 Estilo obligatorio del proyecto

- Mensajes de error **en español**, dentro de `messages()` del FormRequest o en
  el texto de la excepción.
- Errores de dominio: subclases de `App\Errors\ApiException`
  (`NotFoundError` 404, `BadRequestError` 400, `UnauthorizedError` 403,
  `NotAcceptable` 406). Nunca `response()->json()` a pelo.
- Controllers envuelven todo en `try/catch` y devuelven `ResponseHandler::error($th)`.
- `vendor/bin/pint --dirty --format agent` después de tocar PHP.
- Tras cada regla, actualizar `resources/api-docs/openapi.yaml` (respuestas
  `406`/`422` nuevas) — el spec se mantiene a mano.

### 0.3 Hook de validación que se usa en los ejemplos

Los ejemplos usan el hook `after()` del FormRequest (Laravel 11+):

```php
use Illuminate\Validation\Validator;

/**
 * @return array<int, callable>
 */
public function after(): array
{
    return [
        fn (Validator $validator) => $this->validarEquipoDisponible($validator),
    ];
}
```

Si se prefiere el estilo clásico, el mismo cuerpo funciona dentro de
`withValidator(Validator $validator)`.

### 0.4 Reglas reutilizables

Varias reglas se repiten entre `POST /delivery_documents` y
`POST /delivery_document_details`. Hay dos caminos:

1. **Métodos privados en cada FormRequest** (lo que se muestra aquí): cero
   estructura nueva, algo de duplicación.
2. **Objetos `Rule`** con `php artisan make:rule EquipmentIsAvailable
   --no-interaction`, que crea `app/Rules/`. Es la carpeta estándar de Laravel,
   pero **es un directorio base nuevo en este proyecto: pide aprobación antes de
   crearlo** (`CLAUDE.md` lo exige).

### 0.5 Concurrencia

Dos peticiones simultáneas pueden pasar la misma validación y entregar el mismo
equipo dos veces. Para las reglas RN-01, RN-02 y RN-10:

- Ejecutar la comprobación **dentro** de la `DB::transaction` del controller, o
- Apoyarse en el índice único de RN-25 (`return_document_details`), que sí es
  atómico.

---

## 1. Resumen

| ID | Regla | Dónde | Código |
|---|---|---|---|
| RN-01 | Un equipo con entrega activa no se puede volver a entregar | `CreateDeliveryDocumentRequest`, `CreateDeliveryDocumentDetailRequest` | 422 |
| RN-02 | Un empleado no puede tener dos equipos del mismo tipo | idem | 422 |
| RN-03 | No repetir el mismo equipo dentro de la misma entrega | `CreateDeliveryDocumentRequest` | 422 |
| RN-04 | `location` debe ser una planta válida | `CreateDeliveryDocumentRequest`, `UpdateDeliveryDocumentRequest` | 422 |
| RN-05 | No se entrega equipo dado de baja | `CreateDeliveryDocumentRequest` | 422 |
| RN-06 | Las dos firmas deben ser archivos distintos | `CreateDeliveryDocumentRequest`, `CreateReturnDocumentRequest` | 422 |
| RN-07 | Sólo `admin` registra entregas y devoluciones | rutas (middleware) | 403 |
| RN-08 | Cada equipo devuelto debe pertenecer a la entrega indicada | `CreateReturnDocumentRequest` | 422 |
| RN-09 | No repetir el mismo detalle dentro de la misma devolución | `CreateReturnDocumentRequest` | 422 |
| RN-10 | No devolver dos veces el mismo equipo | `CreateReturnDocumentRequest`, `CreateReturnDocumentDetailRequest` | 422 |
| RN-11 | No se registra devolución sobre una entrega ya cerrada | `ReturnDocumentController::store` | 406 |
| RN-12 | La devolución no puede ser anterior a la entrega | `CreateReturnDocumentRequest` | 422 |
| RN-13 | El detalle agregado a una devolución debe ser de su misma entrega | `CreateReturnDocumentDetailRequest` | 422 |
| RN-14 | No se elimina una entrega que ya tiene devoluciones | `DeliveryDocumentController::delete` | 406 |
| RN-15 | No se quita de la entrega un equipo ya devuelto | `DeliveryDocumentDetailController::delete` | 406 |
| RN-16 | No se agregan equipos a una entrega ya cerrada | `CreateDeliveryDocumentDetailRequest` | 422 |
| RN-17 | Las características sólo aplican a equipos que las admiten | `CaracteristicRequest` | 422 |
| RN-18 | Nombre de característica único por equipo | `CaracteristicRequest` | 422 |
| RN-19 | Serie de equipo única | `EquipmentRequest` + migración | 422 |
| RN-20 | Nombre de marca y de departamento únicos | `BrandRequest`, `DepartmentRequest` + migración | 422 |
| RN-21 | Código de empleado único | FormRequest de empleados + migración | 422 |
| RN-22 | No se cambia serie ni tipo de un equipo asignado | `EquipmentController::update` | 406 |
| RN-23 | No se da de baja un equipo asignado | `EquipmentController` (baja) | 406 |
| RN-24 | No se elimina un empleado con equipo asignado | `EmployeeController` (baja) | 406 |
| RN-25 | Un detalle de entrega no puede tener dos devoluciones | migración (índice único) | — |

Además, desde antes: al menos un item por documento, firmas obligatorias
`png/jpg/jpeg` ≤ 2 MB, existencia de empleado/equipo/documento,
`delivery_date`/`return_date`/`user_id` asignados por el servidor.

**Notas de implementación:**

- **RN-02** aplica sólo a los tipos de `TIPOS_UNICOS` (mouse, teclado, laptop,
  desktop, diadema, webcam). Los demás tipos (cable, adaptador, cargador…) no
  tienen tope. La constante está en los dos FormRequests de entrega.
- **RN-11** quedó como última defensa: RN-08 y RN-10 rechazan antes cada detalle
  con `422`, así que el `406` del controller sólo se alcanza en una carrera entre
  dos peticiones simultáneas.
- **RN-12** no se implementó porque `return_date` la sigue poniendo el servidor.
  Si algún día se acepta del cliente, el código de la sección RN-12 aplica.
- **RN-04**: los Resources usan `Plant::tryFrom(...)?->label()` con un
  `'Planta desconocida'` de respaldo, para no reventar con filas viejas que
  guardaron una planta fuera del enum.
- **RN-17**: se agregó `case PHONE = 'phone'` a `EquipmentType` y los teléfonos
  admiten características.

---

## 2. Reglas de la entrega

### RN-01 · Un equipo con entrega activa no se puede volver a entregar

**Por qué.** Si existe un `DeliveryDocumentDetail` del equipo sin su
`ReturnDocumentDetail`, el equipo está en manos de alguien; volver a entregarlo
deja el inventario mintiendo.

**Dónde.** `app/Http/Requests/CreateDeliveryDocumentRequest.php` (items) y
`app/Http/Requests/CreateDeliveryDocumentDetailRequest.php` (equipo suelto).

```php
use App\Models\DeliveryDocumentDetail;
use Illuminate\Validation\Validator;

/**
 * @return array<int, callable>
 */
public function after(): array
{
    return [
        fn (Validator $validator) => $this->validarEquiposDisponibles($validator),
    ];
}

/**
 * RN-01: ningún equipo del documento puede tener una entrega sin devolver.
 */
private function validarEquiposDisponibles(Validator $validator): void
{
    $items = $this->input('items', []);

    $equipmentIds = collect($items)->pluck('equipment_id')->filter()->all();

    if ($equipmentIds === []) {
        return;
    }

    $asignados = DeliveryDocumentDetail::query()
        ->whereIn('equipment_id', $equipmentIds)
        ->whereDoesntHave('returnDetail')
        ->pluck('equipment_id')
        ->all();

    foreach ($items as $index => $item) {
        if (in_array($item['equipment_id'] ?? null, $asignados)) {
            $validator->errors()->add(
                "items.{$index}.equipment_id",
                'El equipo ya está asignado en otra entrega y no se ha devuelto'
            );
        }
    }
}
```

Versión para `CreateDeliveryDocumentDetailRequest` (un solo equipo):

```php
private function validarEquipoDisponible(Validator $validator): void
{
    $tieneEntregaActiva = DeliveryDocumentDetail::query()
        ->where('equipment_id', $this->input('equipment_id'))
        ->whereDoesntHave('returnDetail')
        ->exists();

    if ($tieneEntregaActiva) {
        $validator->errors()->add(
            'equipment_id',
            'El equipo ya está asignado en otra entrega y no se ha devuelto'
        );
    }
}
```

**Tests sugeridos.** Entregar un equipo, intentar entregarlo otra vez → `422`;
devolverlo y volver a entregarlo → `201`.

---

### RN-02 · Un empleado no puede tener dos equipos del mismo tipo

**Por qué.** No tiene sentido asignar dos mouses o dos teclados a la misma
persona. Hay que mirar **dos fuentes**: lo que ya tiene vigente y lo que viene en
la misma petición.

**Dónde.** `CreateDeliveryDocumentRequest` y `CreateDeliveryDocumentDetailRequest`.

```php
use App\Models\DeliveryDocumentDetail;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

/**
 * RN-02: un empleado no puede terminar con dos equipos del mismo tipo.
 */
private function validarTiposRepetidos(Validator $validator): void
{
    $employeeId = $this->input('employee_id');
    $items = $this->input('items', []);

    if (! $employeeId || $items === []) {
        return;
    }

    // Tipos que el empleado ya tiene sin devolver.
    $tiposVigentes = DeliveryDocumentDetail::query()
        ->whereDoesntHave('returnDetail')
        ->whereHas('delivery_documents', function (Builder $document) use ($employeeId) {
            $document->where('employee_id', $employeeId);
        })
        ->with('equipment:id,type')
        ->get()
        ->pluck('equipment.type')
        ->filter()
        ->all();

    $tiposPorEquipo = Equipment::query()
        ->whereIn('id', collect($items)->pluck('equipment_id')->filter())
        ->pluck('type', 'id');

    $tiposEnLaPeticion = [];

    foreach ($items as $index => $item) {
        $type = $tiposPorEquipo[$item['equipment_id'] ?? null] ?? null;

        if (! $type) {
            continue;
        }

        if (in_array($type, $tiposVigentes)) {
            $validator->errors()->add(
                "items.{$index}.equipment_id",
                "El empleado ya tiene asignado un equipo de tipo {$type}"
            );

            continue;
        }

        if (in_array($type, $tiposEnLaPeticion)) {
            $validator->errors()->add(
                "items.{$index}.equipment_id",
                "No se puede entregar más de un equipo de tipo {$type} en la misma entrega"
            );

            continue;
        }

        $tiposEnLaPeticion[] = $type;
    }
}
```

**Decisión pendiente del negocio:** ¿aplica a *todos* los tipos? Si un empleado
puede tener dos monitores o dos cables, define la lista de tipos con tope 1:

```php
use App\Enums\EquipmentType;

/**
 * Tipos de los que un empleado sólo puede tener uno a la vez.
 *
 * @var array<int, EquipmentType>
 */
private const TIPOS_UNICOS = [
    EquipmentType::MOUSE,
    EquipmentType::KEYBOARD,
    EquipmentType::LAPTOP,
    EquipmentType::DESKTOP,
    EquipmentType::HEADSET,
    EquipmentType::WEBCAM,
];
```

y filtra `if (! in_array(EquipmentType::from($type), self::TIPOS_UNICOS)) { continue; }`.

---

### RN-03 · No repetir el mismo equipo dentro de la misma entrega

**Dónde.** `CreateDeliveryDocumentRequest::rules()`. Basta una regla nativa:

```php
'items.*.equipment_id' => ['required', 'distinct', 'exists:equipments,id'],
```

```php
// messages()
'items.*.equipment_id.distinct' => 'No se puede entregar el mismo equipo dos veces en el mismo documento',
```

---

### RN-04 · `location` debe ser una planta válida

**Por qué.** Hoy `location` es un `integer` sin validar: cualquier número entra y
el Resource muestra "Planta Parramos" para todo lo que no sea `1`.

**Dónde.** Crear el enum y usarlo en `CreateDeliveryDocumentRequest` y
`UpdateDeliveryDocumentRequest`.

```php
// app/Enums/Plant.php  (php artisan make:enum Plant --no-interaction)
namespace App\Enums;

enum Plant: int
{
    case Tejar = 1;
    case Parramos = 2;

    public function label(): string
    {
        return match ($this) {
            self::Tejar => 'Planta Tejar',
            self::Parramos => 'Planta Parramos',
        };
    }
}
```

```php
// rules()
use App\Enums\Plant;
use Illuminate\Validation\Rule;

'location' => ['required', 'integer', Rule::enum(Plant::class)],
```

```php
// messages()
'location.enum' => 'La planta seleccionada no es válida',
```

Al implementarlo, cambiar también la traducción de los Resources
(`DeliveryDocumentResource`, `ReturnDocumentResource`, `AssignmentResource`) por
`Plant::from($this->location)->label()`.

---

### RN-05 · No se entrega equipo dado de baja

**Por qué.** `equipments` tiene `softDeletes()` en la migración, pero el modelo
`Equipment` **no usa el trait**, así que hoy no hay bajas reales.

**Paso 1.** Habilitar el trait:

```php
// app/Models/Equipment.php
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;
    // ...
}
```

**Paso 2.** La regla `exists` deja de ver los eliminados automáticamente, pero
conviene el mensaje propio:

```php
use Illuminate\Validation\Rule;

'items.*.equipment_id' => [
    'required',
    'distinct',
    Rule::exists('equipments', 'id')->whereNull('deleted_at'),
],
```

```php
'items.*.equipment_id.exists' => 'El equipo seleccionado no existe o está dado de baja',
```

---

### RN-06 · Las dos firmas deben ser archivos distintos

**Por qué.** Evita que se suba el mismo trazo como firma del responsable y del
administrador.

**Dónde.** `CreateDeliveryDocumentRequest` y `CreateReturnDocumentRequest`.

```php
use Illuminate\Validation\Validator;

private function validarFirmasDistintas(Validator $validator): void
{
    $responsable = $this->file('responsable_signature');
    $administrador = $this->file('administrador_signature');

    if (! $responsable || ! $administrador) {
        return;
    }

    if (hash_file('sha256', $responsable->getRealPath()) === hash_file('sha256', $administrador->getRealPath())) {
        $validator->errors()->add(
            'administrador_signature',
            'La firma del administrador no puede ser la misma que la del responsable'
        );
    }
}
```

---

### RN-07 · Sólo `admin` registra entregas y devoluciones

**Por qué.** Un documento firmado es un registro formal; conviene limitar quién
lo emite. El alias `admin` ya existe en `bootstrap/app.php`.

**Dónde.** Archivos de rutas, sin tocar controllers:

```php
// routes/delivery_documents.php
Route::middleware(['jwt.auth', 'admin'])->group(function () {
    Route::post('/delivery_documents', [DeliveryDocumentController::class, 'store']);
    Route::put('/delivery_documents/{id}', [DeliveryDocumentController::class, 'update']);
    Route::delete('/delivery_documents/{id}', [DeliveryDocumentController::class, 'delete']);
});

Route::middleware('jwt.auth')->group(function () {
    Route::get('/delivery_documents', [DeliveryDocumentController::class, 'index']);
    Route::get('/delivery_documents/{id}', [DeliveryDocumentController::class, 'show']);
    Route::get('/delivery_documents/{id}/pending_items', [DeliveryDocumentController::class, 'pendingItems']);
});
```

Mismo corte en `return_documents.php`. **Decisión del negocio:** confirmar si
las lecturas quedan abiertas a cualquier usuario autenticado (recomendado) o
también se restringen.

---

## 3. Reglas de la devolución

### RN-08 · Cada equipo devuelto debe pertenecer a la entrega indicada

**Por qué.** Hoy `items.*.delivery_document_detail_id` sólo valida que el detalle
exista: se puede devolver contra la entrega #1 un equipo entregado en la #7.

**Dónde.** `app/Http/Requests/CreateReturnDocumentRequest.php`.

```php
use App\Models\DeliveryDocumentDetail;
use Illuminate\Validation\Validator;

/**
 * @return array<int, callable>
 */
public function after(): array
{
    return [
        fn (Validator $validator) => $this->validarDetallesDeLaEntrega($validator),
        fn (Validator $validator) => $this->validarDetallesNoDevueltos($validator),
    ];
}

/**
 * RN-08: los detalles deben ser del documento de entrega enviado.
 */
private function validarDetallesDeLaEntrega(Validator $validator): void
{
    $items = $this->input('items', []);
    $deliveryDocumentId = $this->input('delivery_document_id');

    if (! $deliveryDocumentId || $items === []) {
        return;
    }

    $detallesValidos = DeliveryDocumentDetail::query()
        ->where('delivery_document_id', $deliveryDocumentId)
        ->pluck('id')
        ->all();

    foreach ($items as $index => $item) {
        if (! in_array($item['delivery_document_detail_id'] ?? null, $detallesValidos)) {
            $validator->errors()->add(
                "items.{$index}.delivery_document_detail_id",
                'El equipo no pertenece al documento de entrega indicado'
            );
        }
    }
}
```

---

### RN-09 · No repetir el mismo detalle dentro de la misma devolución

```php
'items.*.delivery_document_detail_id' => [
    'required',
    'distinct',
    'exists:delivery_document_details,id',
],
```

```php
'items.*.delivery_document_detail_id.distinct' => 'No se puede devolver el mismo equipo dos veces en el mismo documento',
```

---

### RN-10 · No devolver dos veces el mismo equipo

**Por qué.** Si el detalle ya tiene `ReturnDocumentDetail`, el equipo ya regresó.

**Dónde.** `CreateReturnDocumentRequest` y `CreateReturnDocumentDetailRequest`.

```php
/**
 * RN-10: el detalle no puede tener ya una devolución registrada.
 */
private function validarDetallesNoDevueltos(Validator $validator): void
{
    $items = $this->input('items', []);

    $yaDevueltos = DeliveryDocumentDetail::query()
        ->whereIn('id', collect($items)->pluck('delivery_document_detail_id')->filter())
        ->whereHas('returnDetail')
        ->pluck('id')
        ->all();

    foreach ($items as $index => $item) {
        if (in_array($item['delivery_document_detail_id'] ?? null, $yaDevueltos)) {
            $validator->errors()->add(
                "items.{$index}.delivery_document_detail_id",
                'El equipo ya fue devuelto en otro documento'
            );
        }
    }
}
```

Versión de un solo detalle para `CreateReturnDocumentDetailRequest`:

```php
private function validarDetalleNoDevuelto(Validator $validator): void
{
    $yaDevuelto = DeliveryDocumentDetail::query()
        ->where('id', $this->input('delivery_document_detail_id'))
        ->whereHas('returnDetail')
        ->exists();

    if ($yaDevuelto) {
        $validator->errors()->add(
            'delivery_document_detail_id',
            'El equipo ya fue devuelto en otro documento'
        );
    }
}
```

---

### RN-11 · No se registra devolución sobre una entrega ya cerrada

**Por qué.** Es estado del documento, no del payload → `406`.

**Dónde.** `app/Http/Controllers/ReturnDocumentController.php`, al inicio de
`store()`, antes de guardar las firmas (para no dejar imágenes huérfanas):

```php
use App\Errors\NotAcceptable;
use App\Models\DeliveryDocument;

$delivery = DeliveryDocument::with('details.returnDetail')->find($data['delivery_document_id']);

if ($delivery->status() === 'devuelto') {
    throw new NotAcceptable('El documento de entrega ya fue devuelto por completo');
}
```

Documentar en el spec la respuesta `406` de `POST /return_documents`.

---

### RN-12 · La devolución no puede ser anterior a la entrega

Hoy `return_date` la pone el servidor, así que la regla **sólo hace falta si
algún día se acepta la fecha desde el cliente**. En ese caso:

```php
use App\Models\DeliveryDocument;

private function validarFechaDeDevolucion(Validator $validator): void
{
    $delivery = DeliveryDocument::find($this->input('delivery_document_id'));

    if (! $delivery || ! $this->input('return_date')) {
        return;
    }

    if ($this->date('return_date')->lt($delivery->delivery_date)) {
        $validator->errors()->add(
            'return_date',
            'La fecha de devolución no puede ser anterior a la de entrega'
        );
    }
}
```

---

### RN-13 · El detalle agregado a una devolución debe ser de su misma entrega

**Dónde.** `app/Http/Requests/CreateReturnDocumentDetailRequest.php` — aquí no
llega `delivery_document_id`, hay que leerlo de la devolución:

```php
use App\Models\DeliveryDocumentDetail;
use App\Models\ReturnDocument;
use Illuminate\Validation\Validator;

private function validarDetalleDeLaMismaEntrega(Validator $validator): void
{
    $returnDocument = ReturnDocument::find($this->input('return_document_id'));
    $detail = DeliveryDocumentDetail::find($this->input('delivery_document_detail_id'));

    if (! $returnDocument || ! $detail) {
        return;
    }

    if ($detail->delivery_document_id !== $returnDocument->delivery_document_id) {
        $validator->errors()->add(
            'delivery_document_detail_id',
            'El equipo no pertenece al documento de entrega de esta devolución'
        );
    }
}
```

---

## 4. Reglas de edición y borrado de documentos

### RN-14 · No se elimina una entrega que ya tiene devoluciones

**Por qué.** `DELETE /delivery_documents/{id}` borra los detalles en cascada; si
hay devoluciones se pierde el rastro del equipo.

**Dónde.** `DeliveryDocumentController::delete()`:

```php
use App\Errors\NotAcceptable;

$delivery_documents = $this->findDeliveryDocumentOrFail($id);

if ($delivery_documents->return_documents()->exists()) {
    throw new NotAcceptable('No se puede eliminar una entrega que ya tiene devoluciones registradas');
}

$delivery_documents->delete();
```

---

### RN-15 · No se quita de la entrega un equipo ya devuelto

**Dónde.** `DeliveryDocumentDetailController::delete()`:

```php
use App\Errors\NotAcceptable;

$delivery_document_details = $this->findDeliveryDocumentDetailOrFail($id);

if ($delivery_document_details->returnDetail) {
    throw new NotAcceptable('No se puede quitar de la entrega un equipo que ya fue devuelto');
}

$delivery_document_details->delete();
```

---

### RN-16 · No se agregan equipos a una entrega ya cerrada

**Dónde.** `CreateDeliveryDocumentDetailRequest`:

```php
use App\Models\DeliveryDocument;
use Illuminate\Validation\Validator;

private function validarEntregaAbierta(Validator $validator): void
{
    $delivery = DeliveryDocument::with('details.returnDetail')->find($this->input('delivery_document_id'));

    if ($delivery && $delivery->status() === 'devuelto') {
        $validator->errors()->add(
            'delivery_document_id',
            'No se pueden agregar equipos a una entrega que ya fue devuelta por completo'
        );
    }
}
```

---

## 5. Reglas del catálogo

### RN-17 · Las características sólo aplican a equipos que las admiten

**Por qué.** Un mouse o un cable no llevan especificaciones; una laptop, un
desktop, una impresora o un teléfono sí.

**Dónde.** `app/Http/Requests/Caracteristic/CaracteristicRequest.php`:

```php
use App\Enums\EquipmentType;
use App\Models\Equipment;
use Illuminate\Validation\Validator;

/**
 * Tipos de equipo que aceptan especificaciones.
 *
 * @var array<int, string>
 */
private const TIPOS_CON_CARACTERISTICAS = [
    EquipmentType::LAPTOP->value,
    EquipmentType::DESKTOP->value,
    EquipmentType::PRINTER->value,
    EquipmentType::MONITOR->value,
];

private function validarTipoDeEquipo(Validator $validator): void
{
    $equipment = Equipment::find($this->input('equipment_id'));

    if ($equipment && ! in_array($equipment->type, self::TIPOS_CON_CARACTERISTICAS)) {
        $validator->errors()->add(
            'equipment_id',
            'El tipo de equipo seleccionado no admite características'
        );
    }
}
```

**Decisión pendiente:** el enum `EquipmentType` no tiene el caso `phone`, y el
proceso sí contempla teléfonos. Agregar `case PHONE = 'phone';` antes de
implementar esta regla.

---

### RN-18 · Nombre de característica único por equipo

```php
use Illuminate\Validation\Rule;

'name' => [
    'required',
    'string',
    'max:255',
    Rule::unique('caracteristics', 'name')
        ->where('equipment_id', $this->input('equipment_id'))
        ->ignore($this->route('id')),
],
```

```php
'name.unique' => 'El equipo ya tiene una característica con ese nombre',
```

---

### RN-19 · Serie de equipo única

**Dónde.** `EquipmentRequest` + migración.

```php
use Illuminate\Validation\Rule;

'serie' => [
    'required',
    'string',
    'max:255',
    Rule::unique('equipments', 'serie')->ignore($this->route('id')),
],
```

```php
'serie.unique' => 'Ya existe un equipo registrado con esa serie',
```

```php
// php artisan make:migration add_unique_serie_to_equipments_table --no-interaction
Schema::table('equipments', function (Blueprint $table) {
    $table->unique('serie');
});
```

---

### RN-20 · Nombre de marca y de departamento únicos

Mismo patrón en `BrandRequest` y `DepartmentRequest`:

```php
'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($this->route('id'))],
```

```php
'name.unique' => 'Ya existe una marca con ese nombre',
```

Más el `$table->unique('name')` en su migración.

---

### RN-21 · Código de empleado único

`EmployeeController` todavía valida inline; **primero** conviene extraer un
`app/Http/Requests/Employee/EmployeeRequest.php` (`php artisan make:request
Employee/EmployeeRequest --no-interaction`) y ahí:

```php
'code' => ['required', 'string', 'max:255', Rule::unique('employees', 'code')->ignore($this->route('id'))],
'name' => ['required', 'string', 'max:255'],
'department_id' => ['required', 'exists:departments,id'],
```

```php
'code.unique' => 'Ya existe un empleado con ese código',
```

---

### RN-22 · No se cambia serie ni tipo de un equipo asignado

**Dónde.** `EquipmentController::update()`:

```php
use App\Errors\NotAcceptable;

$equipment = $this->findEquipmentOrFail($id);

$estaAsignado = $equipment->deliveryDetail()->whereDoesntHave('returnDetail')->exists();
$cambiaIdentidad = $request->validated('serie') !== $equipment->serie
    || $request->validated('type') !== $equipment->type;

if ($estaAsignado && $cambiaIdentidad) {
    throw new NotAcceptable('No se puede cambiar la serie ni el tipo de un equipo que está asignado');
}
```

---

### RN-23 · No se da de baja un equipo asignado

Aplica cuando exista el endpoint de baja (hoy no hay `destroy` en el proyecto).
Con `SoftDeletes` habilitado (RN-05):

```php
use App\Errors\NotAcceptable;

if ($equipment->deliveryDetail()->whereDoesntHave('returnDetail')->exists()) {
    throw new NotAcceptable('No se puede dar de baja un equipo que está asignado');
}

$equipment->delete();
```

---

### RN-24 · No se elimina un empleado con equipo asignado

Mismo criterio, del lado del empleado:

```php
use App\Errors\NotAcceptable;
use App\Models\DeliveryDocumentDetail;
use Illuminate\Database\Eloquent\Builder;

$tieneEquipo = DeliveryDocumentDetail::query()
    ->whereDoesntHave('returnDetail')
    ->whereHas('delivery_documents', function (Builder $document) use ($employee) {
        $document->where('employee_id', $employee->id);
    })
    ->exists();

if ($tieneEquipo) {
    throw new NotAcceptable('No se puede eliminar un empleado que tiene equipo asignado');
}
```

---

### RN-25 · Un detalle de entrega no puede tener dos devoluciones

Última línea de defensa a nivel base de datos para RN-10 (y protege contra
peticiones simultáneas, que la validación por sí sola no cubre):

```php
// php artisan make:migration add_unique_delivery_detail_to_return_document_details_table --no-interaction
Schema::table('return_document_details', function (Blueprint $table) {
    $table->unique('delivery_document_detail_id');
});
```

Ojo: hay que limpiar duplicados existentes antes de aplicarla en producción.

---

## 6. Orden sugerido de implementación

1. **RN-25 y RN-19/20/21** (índices únicos): son migraciones, no rompen nada y
   ponen piso a lo demás.
2. **RN-01, RN-02, RN-03** (entrega): son las reglas que el negocio pidió
   primero y las que más ensucian el inventario.
3. **RN-08, RN-09, RN-10, RN-11** (devolución): cierran el ciclo.
4. **RN-14, RN-15, RN-16** (borrados y correcciones).
5. **RN-04, RN-05, RN-17** (catálogo y enums), que arrastran cambios en Resources
   y en el enum `EquipmentType`.
6. **RN-06, RN-07, RN-22, RN-23, RN-24**: refuerzos, según prioridad del negocio.

## 7. Cobertura de tests esperada

Un test de feature por regla, en `tests/Feature/` (`php artisan make:test --pest
ReglasEntregaTest`), siguiendo el estilo de `AssignmentFlowTest.php`:

- **Camino feliz** que sigue pasando después de la regla.
- **Caso bloqueado**, verificando `status` (`422` o `406`) y el `message` exacto
  en español.
- Para las reglas con estado (RN-11, RN-14, RN-15): montar el escenario completo
  entrega → devolución antes de la aserción.

## 8. Decisiones del negocio

Resueltas al implementar (confirmar con el negocio si alguna no aplica):

1. RN-02: el tope de "uno por tipo" aplica sólo a `TIPOS_UNICOS`, no a todos los
   tipos.
2. RN-04: el enum `Plant` tiene dos plantas, Tejar (`1`) y Parramos (`2`).
3. RN-07: sólo `admin` emite, corrige y elimina documentos; las lecturas quedan
   abiertas a cualquier usuario autenticado.
4. RN-17: sí, se agregó `phone` al enum `EquipmentType`.

Sigue pendiente:

5. ¿Una entrega puede incluir equipo de dos plantas distintas, o la planta del
   documento debe coincidir con la del equipo? (hoy el equipo no guarda planta).
