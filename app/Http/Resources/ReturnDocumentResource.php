<?php

namespace App\Http\Resources;

use App\Enums\Plant;
use App\Interfaces\Storage\ImageStorageServiceInterface;
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
        $delivery = $this->delivery_document;

        return [
            'id' => $this->id,
            'return_date' => $this->return_date->format('d-m-Y h:m:s A'),
            'responsable_signature' => $this->signatureUrl($this->responsable_signature),
            'administrador_signature' => $this->signatureUrl($this->administrador_signature),
            'employee_id' => $delivery->employee->id,
            'employee_name' => $delivery->employee->name,
            'employee_department' => $delivery->employee->department->name,
            'location' => Plant::tryFrom((int) $delivery->location)?->label() ?? 'Planta desconocida',
            'observations' => $this->observations,
            'delivery_document_id' => $this->delivery_document_id,
            'delivery_document_status' => $delivery->status(),
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'items' => ReturnDocumentDetailResource::collection($this->details),
        ];
    }

    /**
     * Convierte la ruta relativa de una firma en la URL pública del disco
     * `filesystems.signatures` (en S3 se arma con `AWS_BUCKET`/`AWS_DEFAULT_REGION`
     * o `AWS_URL`; en `public` con `APP_URL/storage`).
     */
    private function signatureUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        return app(ImageStorageServiceInterface::class)->url($path);
    }
}
