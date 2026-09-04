<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\CreateDeliveryDocumentDetailRequest;
use App\Http\Resources\DeliveryDocumentDetailResource;
use App\Models\DeliveryDocumentDetail;
use Illuminate\Http\Request;

class DeliveryDocumentDetailController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DeliveryDocumentDetail::with(['equipment']);

            if ($request->query('deliveryDocumentId')) {
                $query->where('delivery_document_id', $request->query('deliveryDocumentId'));
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
            $data = $request->validated();
            DeliveryDocumentDetail::create($data);

            return ResponseHandler::success($data, 'Detalles de Documento de Entregas Creado Correctamente', 201);
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
        $delivery_document_details = DeliveryDocumentDetail::find($id);

        if (! $delivery_document_details) {
            throw new NotFoundError('Detalles de Documento de Entrega no encontrado');
        }

        return $delivery_document_details;
    }
}
