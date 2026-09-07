<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\CreateDeliveryDocumentDetailRequest;
use App\Http\Requests\UpdateDeliveryDocumentDetailRequest;
use App\Http\Resources\DeliveryDocumentDetailResource;
use App\Models\DeliveryDocumentDetail;
use Illuminate\Http\Request;

class DeliveryDocumentDetailController extends Controller
{
    /**
     * Relaciones necesarias para armar `DeliveryDocumentDetailResource`.
     *
     * @var array<int, string>
     */
    private const RELATIONS = [
        'equipment.brand',
        'returnDetail',
    ];

    public function index(Request $request)
    {
        try {
            $query = DeliveryDocumentDetail::with(self::RELATIONS);

            if ($request->query('deliveryDocumentId')) {
                $query->where('delivery_document_id', $request->query('deliveryDocumentId'));
            }

            if ($request->query('equipmentId')) {
                $query->where('equipment_id', $request->query('equipmentId'));
            }

            if ($request->query('pending')) {
                $query->whereDoesntHave('returnDetail');
            }

            $delivery_document_details = $query->get();

            $data = DeliveryDocumentDetailResource::collection($delivery_document_details);

            return ResponseHandler::success($data, 'Detalles de Documento de Entregas Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(CreateDeliveryDocumentDetailRequest $request)
    {
        try {
            $delivery_document_detail = DeliveryDocumentDetail::create($request->validated());

            return ResponseHandler::success(new DeliveryDocumentDetailResource($delivery_document_detail->load(self::RELATIONS)), 'Detalles de Documento de Entregas Creado Correctamente', 201);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $delivery_document_details = $this->findDeliveryDocumentDetailOrFail($id);

            return ResponseHandler::success(new DeliveryDocumentDetailResource($delivery_document_details), 'Detalles de Documento de Entrega Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Corrige las observaciones del equipo entregado.
     */
    public function update(UpdateDeliveryDocumentDetailRequest $request, string $id)
    {
        try {
            $delivery_document_details = $this->findDeliveryDocumentDetailOrFail($id);

            $delivery_document_details->update($request->validated());

            return ResponseHandler::success(new DeliveryDocumentDetailResource($delivery_document_details->fresh(self::RELATIONS)), 'Detalles de Documento de Entrega Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function delete(string $id)
    {
        try {
            $delivery_document_details = $this->findDeliveryDocumentDetailOrFail($id);
            $delivery_document_details->delete();

            return ResponseHandler::success(true, 'Detalles de Documento de Entrega Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el documento no existe.
     */
    private function findDeliveryDocumentDetailOrFail(string $id): DeliveryDocumentDetail
    {
        $delivery_document_details = DeliveryDocumentDetail::with(self::RELATIONS)->find($id);

        if (! $delivery_document_details) {
            throw new NotFoundError('Detalles de Documento de Entrega no encontrado');
        }

        return $delivery_document_details;
    }
}
