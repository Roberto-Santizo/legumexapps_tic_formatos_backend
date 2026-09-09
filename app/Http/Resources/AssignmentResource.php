<?php

namespace App\Http\Resources;

use App\Enums\Plant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Asignación de un equipo: un `DeliveryDocumentDetail` con los datos de su
 * documento de entrega y, si ya se devolvió, los de su devolución.
 *
 * La usan `GET /equipments/{id}/history` y `GET /employees/{id}/equipments`.
 */
class AssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $delivery = $this->delivery_documents;
        $returnDetail = $this->returnDetail;
        $returnDocument = $returnDetail?->return_document;

        return [
            'delivery_document_detail_id' => $this->id,
            'delivery_document_id' => $delivery->id,
            'delivery_date' => $delivery->delivery_date->format('d-m-Y h:m:s A'),
            'location' => Plant::tryFrom((int) $delivery->location)?->label() ?? 'Planta desconocida',
            'employee_id' => $delivery->employee->id,
            'employee_name' => $delivery->employee->name,
            'employee_department' => $delivery->employee->department->name,
            'equipment_id' => $this->equipment_id,
            'equipment_name' => $this->equipment->name,
            'equipment_brand' => $this->equipment->brand->name,
            'equipment_model' => $this->equipment->model,
            'equipment_serie' => $this->equipment->serie,
            'equipment_type' => $this->equipment->type,
            'observations' => $this->observations,
            'returned' => $returnDetail !== null,
            'return_document_id' => $returnDocument?->id,
            'return_date' => $returnDocument?->return_date->format('d-m-Y h:m:s A'),
            'return_observations' => $returnDetail?->observations,
        ];
    }
}
