<?php

namespace App\Http\Requests\Equipment;

use App\Enums\EquipmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentRequest extends FormRequest
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
            'model' => 'required|string|max:255',
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'serie' => 'required|string|max:255',
            'original' => 'required|boolean',
            'is_used' => 'required|boolean',
            'type' => ['required', 'string', Rule::enum(EquipmentType::class)],
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
            'model.required' => 'El campo de modelo es obligatorio.',
            'model.string' => 'El modelo debe ser una cadena de texto.',
            'model.max' => 'El modelo no puede tener más de 255 caracteres.',
            'brand_id.required' => 'El campo de marca es obligatorio.',
            'brand_id.integer' => 'La marca debe ser un identificador numérico.',
            'brand_id.exists' => 'La marca seleccionada no existe.',
            'serie.required' => 'El campo de serie es obligatorio.',
            'serie.string' => 'La serie debe ser una cadena de texto.',
            'serie.max' => 'La serie no puede tener más de 255 caracteres.',
            'original.required' => 'El campo de original es obligatorio.',
            'original.boolean' => 'El campo de original debe ser verdadero o falso.',
            'is_used.required' => 'El campo de usado es obligatorio.',
            'is_used.boolean' => 'El campo de usado debe ser verdadero o falso.',
            'type.required' => 'El campo de tipo es obligatorio.',
            'type.string' => 'El tipo debe ser una cadena de texto.',
            'type.enum' => 'El tipo de equipo seleccionado no es válido.',
        ];
    }
}
