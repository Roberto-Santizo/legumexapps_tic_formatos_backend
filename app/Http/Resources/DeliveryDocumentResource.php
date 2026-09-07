<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pending = $this->details->filter(fn ($detail) => $detail->returnDetail === null);

        return [
            'id' => $this->id,
            'location' => $this->location == 1 ? 'Planta Tejar' : 'Planta Parramos',
            'delivery_date' => $this->delivery_date->format('d-m-Y h:m:s A'),
            'responsable_signature' => $this->responsable_signature,
            'administrador_signature' => $this->administrador_signature,
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'employee_department' => $this->employee->department->name,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'observations' => $this->observations,
            'status' => $this->status(),
            'items_count' => $this->details->count(),
            'pending_items_count' => $pending->count(),
            'items' => DeliveryDocumentDetailResource::collection($this->details),
        ];
    }
}
