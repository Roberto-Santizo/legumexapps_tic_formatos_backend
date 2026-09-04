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
        return [
                'id' => $this->id,
                'equipment_id' => $this->id,
                'equipment_name' => $this->delivery_document_details->equipment->name,
                'equipment_brand' => $this->delivery_document_details->equipment->brand->name,
                'equipment_model' => $this->delivery_document_details->equipment->model,
                'equipment_serie' => $this->delivery_document_details->equipment->serie,
                'is_used' => $this->delivery_document_details->equipment->is_used ? 'Usado' : 'Nuevo',
                'original' => $this->delivery_document_details  ->equipment->original ? 'Original' : 'Copia',
                'observations' => $this->observations,
                'delivery_document_detail_id' => $this->delivery_document_details->id
        ];
    }
}
