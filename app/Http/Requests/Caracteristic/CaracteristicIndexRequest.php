<?php

namespace App\Http\Requests\Caracteristic;

use App\Http\Requests\PaginatedIndexRequest;

class CaracteristicIndexRequest extends PaginatedIndexRequest
{
    /**
     * Filtros opcionales de `GET /caracteristics`.
     *
     * @return array<string, mixed>
     */
    protected function filterRules(): array
    {
        return [
            'equipmentId' => ['nullable', 'integer', 'exists:equipments,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function filterMessages(): array
    {
        return [
            'equipmentId.integer' => 'El equipo debe ser un identificador numérico.',
            'equipmentId.exists' => 'El equipo seleccionado no existe.',
        ];
    }
}
