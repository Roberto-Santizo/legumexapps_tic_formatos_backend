<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\ReturnDocumentDetails\ReturnDocumentDetailRequest;
use App\Models\ReturnDocumentDetail;

class ReturnDocumentDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $return_document_details = ReturnDocumentDetail::all();

            return ResponseHandler::success($return_document_details, 'Detalle de Devolución de Documento Obtenidas Correctamente', 200);
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
            $return_document_details = ReturnDocumentDetail::create($request->validated());

            return ResponseHandler::success($return_document_details, 'Detalle de Devolución de Documento Creada Correctamente', 201);
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

            return ResponseHandler::success($return_document_details, 'Detalle de Devolución de Documento Obtenida Correctamente', 200);
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
            $return_document_details = $this->findReturnDocumentDetailOrFail($id);

            $return_document_details->update($request->validated());

            return ResponseHandler::success($return_document_details, 'Detalle de Devolución de Documento Actualizada Correctamente', 200);
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
            throw new NotFoundError('Marca no encontrada');
        }

        return $return_document_details;
    }
}
