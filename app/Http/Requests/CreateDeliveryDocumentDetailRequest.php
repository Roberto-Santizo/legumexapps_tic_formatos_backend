<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
}
