<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class ReturnDocumentDetailIndexRequest extends PaginatedIndexRequest
{
    /**
     * Filtros opcionales de `GET /return_document_details`.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function filterRules(): array
    {
        return [
            'returnDocumentId' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function filterMessages(): array
    {
        return [
            'returnDocumentId.integer' => 'El documento de devolución debe ser un identificador numérico',
        ];
    }
}
