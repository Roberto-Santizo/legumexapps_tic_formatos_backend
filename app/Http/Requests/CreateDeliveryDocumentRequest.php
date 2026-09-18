<?php

namespace App\Http\Requests;

use App\Enums\Plant;
use App\Models\DeliveryDocumentDetail;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateDeliveryDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location' => ['required', 'integer', Rule::enum(Plant::class)],
            'responsable_signature' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:2048'],
            'administrador_signature' => ['required', 'mimes:png,jpg,jpeg', 'max:2048'],
            'employee_id' => ['required', 'exists:employees,id'],
            'observations' => ['nullable'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.equipment_id' => ['required', 'distinct', Rule::exists('equipments', 'id')->whereNull('deleted_at')],
            'items.*.observations' => ['nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'location.required' => 'La planta es requerida',
            'location.enum' => 'La planta seleccionada no es válida',
            'responsable_signature.required' => 'La firma del responsable es requerida',
            'administrador_signature.required' => 'La firma del administrador es requerida',
            'employee_id.required' => 'El empleado es requerido',
            'employee_id.exists' => 'El empleado seleccionado no existe',
            'items.required' => 'Debe entregar al menos un equipo',
            'items.min' => 'Debe entregar al menos un equipo',
            'items.*.equipment_id.required' => 'Cada equipo entregado debe indicar el equipo',
            'items.*.equipment_id.distinct' => 'No se puede entregar el mismo equipo dos veces en el mismo documento',
            'items.*.equipment_id.exists' => 'El equipo seleccionado no existe o está dado de baja',
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validarEquiposDisponibles($validator),
            fn (Validator $validator) => $this->validarFirmasDistintas($validator),
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
     * RN-06: las dos firmas no pueden ser el mismo archivo.
     */
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
}
