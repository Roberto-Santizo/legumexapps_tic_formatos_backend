<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class DeliveryDocumentDetailIndexRequest extends PaginatedIndexRequest
{
    /**
     * Filtros opcionales de `GET /delivery_document_details`.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return [
            'deliveryDocumentId' => ['nullable', 'integer'],
            'equipmentId' => ['nullable', 'integer'],
            'pending' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function filterMessages(): array
    {
        return [
            'deliveryDocumentId.integer' => 'El documento de entrega debe ser un identificador numérico',
            'equipmentId.integer' => 'El equipo debe ser un identificador numérico',
            'pending.boolean' => 'El filtro de pendientes debe ser verdadero o falso',
        ];
    }
}
