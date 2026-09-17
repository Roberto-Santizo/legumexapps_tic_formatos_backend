<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Vista reducida de una característica para el listado: `id`, `name` y el
 * nombre del equipo al que pertenece.
 */
class CaracteristicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'equipment' => $this->equipment->name,
        ];
    }
}
