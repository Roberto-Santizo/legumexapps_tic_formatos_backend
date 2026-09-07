<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryDocumentDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $returnDetail = $this->returnDetail;

        return [
            'id' => $this->id,
            'delivery_document_id' => $this->delivery_document_id,
            'equipment_id' => $this->equipment_id,
            'equipment_name' => $this->equipment->name,
            'equipment_brand' => $this->equipment->brand->name,
            'equipment_model' => $this->equipment->model,
            'equipment_serie' => $this->equipment->serie,
            'equipment_type' => $this->equipment->type,
            'is_used' => $this->equipment->is_used ? 'Usado' : 'Nuevo',
            'original' => $this->equipment->original ? 'Original' : 'Copia',
            'observations' => $this->observations,
            'returned' => $returnDetail !== null,
            'return_document_id' => $returnDetail?->return_document_id,
        ];
    }
}
