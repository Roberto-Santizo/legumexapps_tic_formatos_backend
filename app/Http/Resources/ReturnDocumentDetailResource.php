<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturnDocumentDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $deliveryDetail = $this->delivery_document_details;

        return [
            'id' => $this->id,
            'return_document_id' => $this->return_document_id,
            'delivery_document_detail_id' => $deliveryDetail->id,
            'equipment_id' => $deliveryDetail->equipment_id,
            'equipment_name' => $deliveryDetail->equipment->name,
            'equipment_brand' => $deliveryDetail->equipment->brand->name,
            'equipment_model' => $deliveryDetail->equipment->model,
            'equipment_serie' => $deliveryDetail->equipment->serie,
            'equipment_type' => $deliveryDetail->equipment->type,
            'is_used' => $deliveryDetail->equipment->is_used ? 'Usado' : 'Nuevo',
            'original' => $deliveryDetail->equipment->original ? 'Original' : 'Copia',
            'observations' => $this->observations,
        ];
    }
}
