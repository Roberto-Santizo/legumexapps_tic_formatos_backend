<?php

namespace App\Http\Requests\Caracteristic;

use App\Enums\EquipmentType;
use App\Models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CaracteristicRequest extends FormRequest
{
    /**
     * RN-17: tipos de equipo que aceptan especificaciones.
     *
     * @var array<int, string>
     */
    private const TIPOS_CON_CARACTERISTICAS = [
        EquipmentType::LAPTOP->value,
        EquipmentType::DESKTOP->value,
        EquipmentType::PRINTER->value,
        EquipmentType::MONITOR->value,
        EquipmentType::PHONE->value,
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('caracteristics', 'name')
                    ->where('equipment_id', $this->input('equipment_id'))
                    ->ignore($this->route('id')),
            ],
            'description' => 'required|string|max:255',
            'equipment_id' => ['required', 'integer', 'exists:equipments,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El campo de nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'description.required' => 'El campo de descripción es obligatorio.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no puede tener más de 255 caracteres.',
            'equipment_id.required' => 'El campo de equipo es obligatorio.',
            'equipment_id.integer' => 'El equipo debe ser un identificador numérico.',
            'equipment_id.exists' => 'El equipo seleccionado no existe.',
            'name.unique' => 'El equipo ya tiene una característica con ese nombre',
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validarTipoDeEquipo($validator),
        ];
    }

    /**
     * RN-17: no todos los tipos de equipo admiten especificaciones.
     */
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
}
