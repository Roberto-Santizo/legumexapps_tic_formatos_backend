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
            'return_date' => $this->return_date,
            'responsable_signature' => $this->responsable_signature,
            'administrador_signature' => $this->administrador_signature,
            'observations' => $this->observations,
            'delivery_document_id' => $this ->delivery_documents->delivery_document_id
        ];
    }
}
