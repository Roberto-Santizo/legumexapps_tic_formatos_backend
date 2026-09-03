<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Resources\DeliveryDocumentDetailResource;
use App\Models\DeliveryDocumentDetail;
use Illuminate\Http\Request;

class DeliveryDocumentController extends Controller
{
    public function index()
    {
        try {
            $delivery_document_details = DeliveryDocumentDetail::with(['employee_id', 'user_id'])->get();
            $data = DeliveryDocumentDetailResource::collection($delivery_document_details);
            
            return ResponseHandler::success($data, 'Detalles de Documentos de Entrega Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'id' => 'required',
                'delivery_document_id' => 'required',
                'equipment_id' => 'required',
                'observations' => 'required'
            ]);

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

            return ResponseHandler::success($delivery_document_details, 'Documento de Entrega Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = $request->validate([
                'id' => 'required',
                'delivery_document_id' => 'required',
                'equipment_id' => 'required',
                'observations' => 'required'
                ]);
            $delivery_document_details = DeliveryDocumentDetail::find($id);

            $delivery_document_details->update($data);

            return ResponseHandler::success($delivery_document_details, 'Empleado Actualizado Correctamente', 200);
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
            throw new NotFoundError('Documento de Entrega no encontrado');
        }

        return $delivery_document_details;
    }
}
