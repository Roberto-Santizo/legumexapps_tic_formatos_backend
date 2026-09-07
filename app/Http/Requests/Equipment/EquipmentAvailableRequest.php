<?php

namespace App\Http\Requests\Equipment;

use App\Enums\EquipmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentAvailableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Filtros opcionales de `GET /equipments/available`.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', Rule::enum(EquipmentType::class)],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.enum' => 'El tipo de equipo seleccionado no es válido.',
            'search.max' => 'La búsqueda no puede tener más de 255 caracteres.',
        ];
    }
}
