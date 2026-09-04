<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lastDelivery = $this->deliveryDetail->sortByDesc('created_at')->first();
        return [
                'id' => $this->id,
                'name' => $this->name,
                'brand' => $this->brand->name,
                'registeredBy' => $this->user->name,
                'original' => $this->original ? 'Nuevo' : 'Usado',
                'isAssigned' => $lastDelivery !== null && $lastDelivery->returnDetail === null
        
        ];
    }
}
    