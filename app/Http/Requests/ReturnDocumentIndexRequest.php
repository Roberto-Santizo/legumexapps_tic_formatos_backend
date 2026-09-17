<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class ReturnDocumentIndexRequest extends PaginatedIndexRequest
{
    /**
     * Filtros opcionales de `GET /return_documents`.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return [
            'deliveryDocumentId' => ['nullable', 'exists:delivery_documents,id'],
            'employeeId' => ['nullable', 'exists:employees,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function filterMessages(): array
    {
        return [
            'deliveryDocumentId.exists' => 'El documento de entrega seleccionado no existe',
            'employeeId.exists' => 'El empleado seleccionado no existe',
        ];
    }
}
