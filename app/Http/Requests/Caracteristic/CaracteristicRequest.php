<?php

namespace App\Http\Requests\Caracteristic;

use Illuminate\Foundation\Http\FormRequest;

class CaracteristicRequest extends FormRequest
{
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
            'name' => 'required|string|max:255',
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
        ];
    }
}
