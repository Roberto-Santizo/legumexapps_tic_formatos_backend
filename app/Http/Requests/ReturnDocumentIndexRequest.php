<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReturnDocumentIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Filtros opcionales de `GET /return_documents`.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'deliveryDocumentId' => ['nullable', 'exists:delivery_documents,id'],
            'employeeId' => ['nullable', 'exists:employees,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'deliveryDocumentId.exists' => 'El documento de entrega seleccionado no existe',
            'employeeId.exists' => 'El empleado seleccionado no existe',
        ];
    }
}
