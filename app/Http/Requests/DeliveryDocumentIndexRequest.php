<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class DeliveryDocumentIndexRequest extends PaginatedIndexRequest
{
    /**
     * Filtros opcionales de `GET /delivery_documents`.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
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
    protected function filterMessages(): array
    {
        return [
            'employeeId.exists' => 'El empleado seleccionado no existe',
            'location.integer' => 'La planta debe ser un valor numérico',
            'status.in' => 'El estado debe ser pendiente, parcial, devuelto o activo',
        ];
    }
}
