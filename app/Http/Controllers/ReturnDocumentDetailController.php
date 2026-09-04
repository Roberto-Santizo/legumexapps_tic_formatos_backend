<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\ReturnDocumentDetail\ReturnDocumentDetailRequest;
use App\Http\Resources\ReturnDocumentDetailResource;
use App\Models\ReturnDocumentDetail;

class ReturnDocumentDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $return_document_details = ReturnDocumentDetail::with(['delivery_document_detail_id'])->get();
            $data = ReturnDocumentDetailResource::collection($return_document_details);
            
            return ResponseHandler::success($data, 'Detalles de Devolución de Documentos Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReturnDocumentDetail $request)
    {
        try {
            $data = $request->validate([
                'observation' => ['nullable'],
                'delivery_document_detail_id' => ['required', 'exists:delivery_document_details,id']   
            ]);

            ReturnDocumentDetail::create($data);
            
            return ResponseHandler::success($data, 'Detalles de Devolución de Documento Creados Correctamente', 201);
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
            $return_document_details = $this->findReturnDocumentDetailOrFail($id);

            return ResponseHandler::success($return_document_details, 'Detalles de Devolución de Documento Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReturnDocumentDetail $request, string $id)
    {
        try {
            $data = $request->validate([
                'observation' => ['nullable'],
                'delivery_document_detail_id' => ['required', 'exists:delivery_document_details,id']   
            ]);

            ReturnDocumentDetail::create($data);
            
            return ResponseHandler::success($data, 'Detalles de Devolución de Documento Actualizado Correctamente', 201);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si la marca no existe.
     */
    private function findReturnDocumentDetailOrFail(string $id): ReturnDocumentDetail
    {
        $return_document_details = ReturnDocumentDetail::find($id);

        if (! $return_document_details) {
            throw new NotFoundError('Detalles de Devolución de Documentos no encontrados');
        }

        return $return_document_details;
    }
}
