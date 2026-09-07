<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateReturnDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Cuerpo `multipart/form-data` de `POST /return_documents`. Los equipos que
     * se devuelven viajan en `items`, cada uno apuntando al detalle de la
     * entrega original: sin entrega no puede haber devolución.
     *
     * `return_date` y `user_id` los asigna el servidor.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'responsable_signature' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:2048'],
            'administrador_signature' => ['required', 'mimes:png,jpg,jpeg', 'max:2048'],
            'observations' => ['nullable'],
            'delivery_document_id' => ['required', 'exists:delivery_documents,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.delivery_document_detail_id' => ['required', 'exists:delivery_document_details,id'],
            'items.*.observations' => ['nullable'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'responsable_signature.required' => 'La firma del responsable es requerida',
            'administrador_signature.required' => 'La firma del administrador es requerida',
            'delivery_document_id.required' => 'El documento de entrega es requerido',
            'delivery_document_id.exists' => 'El documento de entrega seleccionado no existe',
            'items.required' => 'Debe devolver al menos un equipo',
            'items.min' => 'Debe devolver al menos un equipo',
            'items.*.delivery_document_detail_id.required' => 'Cada equipo devuelto debe indicar el detalle de la entrega',
            'items.*.delivery_document_detail_id.exists' => 'El detalle de entrega seleccionado no existe',
        ];
    }
}
