<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\CreateReturnDocumentRequest;
use App\Http\Requests\ReturnDocumentIndexRequest;
use App\Http\Requests\UpdateReturnDocumentRequest;
use App\Http\Resources\ReturnDocumentResource;
use App\Interfaces\Storage\ImageStorageServiceInterface;
use App\Models\ReturnDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Errors\NotAcceptable;
use App\Models\DeliveryDocument;

class ReturnDocumentController extends Controller
{
    /**
     * Relaciones necesarias para armar `ReturnDocumentResource`.
     *
     * @var array<int, string>
     */
    private const RELATIONS = [
        'delivery_document.employee.department',
        'delivery_document.details.returnDetail',
        'user',
        'details.delivery_document_details.equipment.brand',
    ];

    /**
     * Listado de devoluciones. Acepta los filtros `deliveryDocumentId` y
     * `employeeId`.
     */
    public function index(ReturnDocumentIndexRequest $request)
    {
        try {
            $query = ReturnDocument::with(self::RELATIONS);

            if ($request->validated('deliveryDocumentId')) {
                $query->where('delivery_document_id', $request->validated('deliveryDocumentId'));
            }

            if ($request->validated('employeeId')) {
                $employeeId = $request->validated('employeeId');

                $query->whereHas('delivery_document', function (Builder $document) use ($employeeId) {
                    $document->where('employee_id', $employeeId);
                });
            }

            $return_documents = $query->orderByDesc('id')->get();
            $data = ReturnDocumentResource::collection($return_documents);

            return ResponseHandler::success($data, 'Devolución de Documentos Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Crea la devolución y sus detalles en una sola transacción. La devolución
     * puede ser parcial: sólo se envían los equipos que regresan.
     */
    public function store(CreateReturnDocumentRequest $request, ImageStorageServiceInterface $imageStorage)
    {
        try {
            $data = $request->validated();

            $items = $data['items'];
            unset($data['items']);

            $data['return_date'] = Carbon::now();
            $data['user_id'] = auth()->user()->id;

            $delivery = DeliveryDocument::with('details.returnDetail')->find($data['delivery_document_id']);

            if ($delivery->status() === 'devuelto') {
                throw new NotAcceptable('El documento de entrega ya fue devuelto por completo');
            }


            $data['responsable_signature'] = $imageStorage->store($request->file('responsable_signature'));
            $data['administrador_signature'] = $imageStorage->store($request->file('administrador_signature'));

            DB::transaction(function () use ($data, $items) {
                $return_document = ReturnDocument::create($data);
                $return_document->details()->createMany($items);
            });

            return ResponseHandler::success(true, 'Devolución de Documento Creado Correctamente', 201);
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
            $return_documents = $this->findReturnDocumentOrFail($id);

            return ResponseHandler::success(new ReturnDocumentResource($return_documents), 'Devolución de Documento Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Corrige las observaciones de una devolución ya firmada.
     */
    public function update(UpdateReturnDocumentRequest $request, string $id)
    {
        try {
            $return_documents = $this->findReturnDocumentOrFail($id);

            $return_documents->update($request->validated());

            return ResponseHandler::success(new ReturnDocumentResource($return_documents->fresh(self::RELATIONS)), 'Devolución de Documento Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el documento no existe.
     */
    private function findReturnDocumentOrFail(string $id): ReturnDocument
    {
        $return_documents = ReturnDocument::with(self::RELATIONS)->find($id);

        if (! $return_documents) {
            throw new NotFoundError('Devolución de Documento no encontrada');
        }

        return $return_documents;
    }
}
