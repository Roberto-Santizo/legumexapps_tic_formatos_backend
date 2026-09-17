<?php

namespace App\Http\Resources;

use App\Enums\Plant;
use App\Interfaces\Storage\ImageStorageServiceInterface;
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
            'location' => Plant::tryFrom((int) $this->location)?->label() ?? 'Planta desconocida',
            'delivery_date' => $this->delivery_date,
            'responsable_signature' => $this->signatureUrl($this->responsable_signature),
            'administrador_signature' => $this->signatureUrl($this->administrador_signature),
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
