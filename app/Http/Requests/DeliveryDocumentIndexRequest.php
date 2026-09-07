<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryDocumentIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Filtros opcionales de `GET /delivery_documents`.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employeeId' => ['nullable', 'exists:employees,id'],
            'location' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['pendiente', 'parcial', 'devuelto', 'activo'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employeeId.exists' => 'El empleado seleccionado no existe',
            'location.integer' => 'La planta debe ser un valor numérico',
            'status.in' => 'El estado debe ser pendiente, parcial, devuelto o activo',
        ];
    }
}
