<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\DeliveryDocumentDetail;
use App\Models\DeliveryDocument;
use Illuminate\Validation\Validator;
use App\Enums\EquipmentType;


class CreateDeliveryDocumentDetailRequest extends FormRequest
{
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
            'equipment_id' => ['required', 'exists:equipments,id'],
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
            'equipment_id.exists' => 'El equipo seleccionado no existe',
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn(Validator $validator) => $this->validarEquiposDisponibles($validator),
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
