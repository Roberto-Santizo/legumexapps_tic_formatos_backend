<?php

namespace App\Http\Requests\Caracteristic;

use Illuminate\Foundation\Http\FormRequest;

class CaracteristicIndexRequest extends FormRequest
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
            'equipmentId' => ['nullable', 'integer', 'exists:equipments,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'equipmentId.integer' => 'El equipo debe ser un identificador numérico.',
            'equipmentId.exists' => 'El equipo seleccionado no existe.',
        ];
    }
}
