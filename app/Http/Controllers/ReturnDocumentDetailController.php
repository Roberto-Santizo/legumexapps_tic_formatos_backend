<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\CreateReturnDocumentDetailRequest;
use App\Http\Requests\UpdateReturnDocumentDetailRequest;
use App\Http\Resources\ReturnDocumentDetailResource;
use App\Models\ReturnDocumentDetail;
use Illuminate\Http\Request;

class ReturnDocumentDetailController extends Controller
{
    /**
     * Relaciones necesarias para armar `ReturnDocumentDetailResource`.
     *
     * @var array<int, string>
     */
    private const RELATIONS = [
        'delivery_document_details.equipment.brand',
    ];

    /**
     * Display a listing of the resource. Acepta el filtro `returnDocumentId`.
     */
    public function index(Request $request)
    {
        try {
            $query = ReturnDocumentDetail::with(self::RELATIONS);

            if ($request->query('returnDocumentId')) {
                $query->where('return_document_id', $request->query('returnDocumentId'));
            }

            $return_document_details = $query->get();
            $data = ReturnDocumentDetailResource::collection($return_document_details);

            return ResponseHandler::success($data, 'Detalles de Devolución de Documentos Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateReturnDocumentDetailRequest $request)
    {
        try {
            $return_document_detail = ReturnDocumentDetail::create($request->validated());

            return ResponseHandler::success(new ReturnDocumentDetailResource($return_document_detail->load(self::RELATIONS)), 'Detalles de Devolución de Documentos Creados Correctamente', 201);
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

            return ResponseHandler::success(new ReturnDocumentDetailResource($return_document_details), 'Detalles de Devolución de Documento Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Corrige las observaciones del equipo devuelto.
     */
    public function update(UpdateReturnDocumentDetailRequest $request, string $id)
    {
        try {
            $return_document_details = $this->findReturnDocumentDetailOrFail($id);

            $return_document_details->update($request->validated());

            return ResponseHandler::success(new ReturnDocumentDetailResource($return_document_details->fresh(self::RELATIONS)), 'Detalles de Devolución de Documento Actualizados Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el detalle no existe.
     */
    private function findReturnDocumentDetailOrFail(string $id): ReturnDocumentDetail
    {
        $return_document_details = ReturnDocumentDetail::with(self::RELATIONS)->find($id);

        if (! $return_document_details) {
            throw new NotFoundError('Detalles de Devolución de Documentos no encontrados');
        }

        return $return_document_details;
    }
}
