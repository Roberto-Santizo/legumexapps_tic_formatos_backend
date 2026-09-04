<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturnDocumentResource extends JsonResource
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
            'return_date' => $this->return_date->format('d-m-Y h:m:s A'),
            'responsable_signature' => $this->responsable_signature,
            'administrador_signature' => $this->administrador_signature,
            'employee_id' => $this->delivery_document->employee->id,
            'employee_name' => $this->delivery_document->employee->name,
            'employee_department' => $this->delivery_document->employee->department->name,
            'location' => $this->location == 1 ? 'Planta Tejar' : 'Planta Parramos',
            'observations' => $this->observations,
            'delivery_document_id' => $this->delivery_document_id,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'items' => ReturnDocumentDetailResource::collection($this->details)
        ];
    }
}
