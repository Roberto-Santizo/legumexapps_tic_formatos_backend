<?php

namespace App\Http\Requests;

use App\Enums\EquipmentType;
use App\Models\DeliveryDocument;
use App\Models\DeliveryDocumentDetail;
use App\Models\Equipment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateDeliveryDocumentDetailRequest extends FormRequest
{
    /**
     * RN-02: tipos de los que un empleado sólo puede tener uno a la vez.
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

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Agrega un equipo a un documento de entrega ya creado.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'delivery_document_id' => ['required', 'exists:delivery_documents,id'],
            'equipment_id' => ['required', Rule::exists('equipments', 'id')->whereNull('deleted_at')],
            'observations' => ['nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'delivery_document_id.required' => 'El documento de entrega es requerido',
            'delivery_document_id.exists' => 'El documento de entrega seleccionado no existe',
            'equipment_id.required' => 'El equipo es requerido',
            'equipment_id.exists' => 'El equipo seleccionado no existe o está dado de baja',
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validarEquipoDisponible($validator),
            fn (Validator $validator) => $this->validarTipoRepetido($validator),
            fn (Validator $validator) => $this->validarEntregaAbierta($validator),
        ];
    }

    /**
     * RN-01: el equipo no puede tener una entrega sin devolver.
     */
    private function validarEquipoDisponible(Validator $validator): void
    {
        if (! $this->input('equipment_id')) {
            return;
        }

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

    /**
     * RN-02: el empleado de la entrega no puede terminar con dos equipos del
     * mismo tipo.
     */
    private function validarTipoRepetido(Validator $validator): void
    {
        $delivery = DeliveryDocument::find($this->input('delivery_document_id'));
        $equipment = Equipment::find($this->input('equipment_id'));

        if (! $delivery || ! $equipment || ! $this->tieneTopeDeUno((string) $equipment->type)) {
            return;
        }

        $employeeId = $delivery->employee_id;

        $yaTieneElTipo = DeliveryDocumentDetail::query()
            ->whereDoesntHave('returnDetail')
            ->whereHas('delivery_documents', function (Builder $document) use ($employeeId) {
                $document->where('employee_id', $employeeId);
            })
            ->whereHas('equipment', function (Builder $item) use ($equipment) {
                $item->where('type', $equipment->type);
            })
            ->exists();

        if ($yaTieneElTipo) {
            $validator->errors()->add(
                'equipment_id',
                "El empleado ya tiene asignado un equipo de tipo {$equipment->type}"
            );
        }
    }

    /**
     * RN-02: sólo los tipos de `TIPOS_UNICOS` están limitados a uno por empleado.
     */
    private function tieneTopeDeUno(string $type): bool
    {
        $equipmentType = EquipmentType::tryFrom($type);

        return $equipmentType !== null && in_array($equipmentType, self::TIPOS_UNICOS, true);
    }

    /**
     * RN-16: no se agregan equipos a una entrega ya cerrada.
     */
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
}
