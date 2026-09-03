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
        return [
            'id' => $this->id,
            'location' => $this->location,
            'delivery_date' => $this->delivery_date,
            'responsable_signature' => $this->responsable_signature,
            'administrador_signature' => $this->administrador_signature,
            'employee_id' => $this->employees->employee_id,
            'user_id' => $this->users->user_id,
            'observations' => $this->observations
        ];
    }
}
