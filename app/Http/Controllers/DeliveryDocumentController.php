<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Resources\DeliveryDocumentResource;
use App\Models\DeliveryDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeliveryDocumentController extends Controller
{
    public function index()
    {
        try {
            $delivery_documents = DeliveryDocument::with(['employee_id', 'user_id'])->get();
            $data = DeliveryDocumentResource::collection($delivery_documents);
            
            return ResponseHandler::success($data, 'Documentos de Entrega Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'location' => 'required',
                'delivery_date' => 'required',
                'responsable_signature' => ['required', 'max:2048'],
                'administrador_signature' => ['required', 'max:2048'],
                'employee_id' => ['required', 'exists:employees,id'],
                'user_id' => ['required', 'exists:users,id'],
                'observations' => ['nullable']
            ]);

            $filename = Str::uuid() . '.png';
            $filename2 = Str::uuid() . '.png';
            
            $data['responsable_signature'] = $filename;
            $data['administrador_signature'] = $filename2;

            DeliveryDocument::create($data);

            return ResponseHandler::success($data, 'Documento de Entregas Creado Correctamente', 201);
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
            $delivery_documents = $this->findDeliveryDocumentOrFail($id);

            return ResponseHandler::success($delivery_documents, 'Documento de Entrega Obtenido Correctamente', 200);
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
                'location' => 'required',
                'delivery_date' => 'required',
                'responsable_signature' => ['required', 'mimes:png,jpg,jpeg', 'max:2048'],
                'administrador_signature' => ['required', 'mimes:png,jpg,jpeg', 'max:2048'],
                'employee_id' => ['required', 'exists:employee,id'],
                'user_id' => ['required', 'exists:user,id'],
                'observations' => 'nullable|string'
                ]);
            $delivery_documents = DeliveryDocument::find($id);

            $delivery_documents->update($data);

            return ResponseHandler::success($delivery_documents, 'Documento de Entregas Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el documento no existe.
     */
    private function findDeliveryDocumentOrFail(string $id): DeliveryDocument
    {
        $delivery_documents = DeliveryDocument::find($id);

        if (! $delivery_documents) {
            throw new NotFoundError('Documento de Entrega no encontrado');
        }

        return $delivery_documents;
    }
}
