<?php

namespace App\Http\Controllers;

use App\Errors\NotAcceptable;
use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\CreateDeliveryDocumentRequest;
use App\Http\Requests\DeliveryDocumentIndexRequest;
use App\Http\Requests\UpdateDeliveryDocumentRequest;
use App\Http\Resources\DeliveryDocumentDetailResource;
use App\Http\Resources\DeliveryDocumentResource;
use App\Interfaces\Storage\ImageStorageServiceInterface;
use App\Models\DeliveryDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryDocumentController extends Controller
{
    /**
     * Relaciones necesarias para armar `DeliveryDocumentResource`.
     *
     * @var array<int, string>
     */
    private const RELATIONS = [
        'employee.department',
        'user',
        'details.equipment.brand',
        'details.returnDetail',
    ];

    /**
     * Listado de entregas. Acepta los filtros `employeeId`, `location` y
     * `status` (`pendiente`, `parcial`, `devuelto` o `activo`).
     */
    public function index(DeliveryDocumentIndexRequest $request)
    {
        try {
            $query = DeliveryDocument::with(self::RELATIONS);

            if ($request->validated('employeeId')) {
                $query->where('employee_id', $request->validated('employeeId'));
            }

            if ($request->validated('location') !== null) {
                $query->where('location', $request->validated('location'));
            }

            if ($request->validated('status')) {
                $this->applyStatusFilter($query, $request->validated('status'));
            }

            $delivery_documents = $query->orderByDesc('id')->get();
            $data = DeliveryDocumentResource::collection($delivery_documents);

            return ResponseHandler::success($data, 'Documentos de Entrega Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(CreateDeliveryDocumentRequest $request, ImageStorageServiceInterface $imageStorage)
    {
        try {
            $data = $request->validated();

            $items = $data['items'];
            unset($data['items']);

            $data['user_id'] = auth()->user()->id;
            $data['delivery_date'] = Carbon::now();

            $data['responsable_signature'] = $imageStorage->store($request->file('responsable_signature'));
            $data['administrador_signature'] = $imageStorage->store($request->file('administrador_signature'));

            DB::transaction(function () use ($data, $items) {
                $delivery_document = DeliveryDocument::create($data);
                $delivery_document->details()->createMany($items);
            });

            return ResponseHandler::success(true, 'Documento de Entregas Creado Correctamente', 201);
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

            return ResponseHandler::success(new DeliveryDocumentResource($delivery_documents), 'Documento de Entrega Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Corrige el encabezado (planta y observaciones) de una entrega ya firmada.
     */
    public function update(UpdateDeliveryDocumentRequest $request, string $id)
    {
        try {
            $delivery_document = $this->findDeliveryDocumentOrFail($id);

            $delivery_document->update($request->validated());

            return ResponseHandler::success(new DeliveryDocumentResource($delivery_document->fresh(self::RELATIONS)), 'Documento de Entrega Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Equipos de la entrega que todavía no se han devuelto. Es la lista con la
     * que se arma una devolución parcial.
     */
    public function pendingItems(string $id)
    {
        try {
            $delivery_document = $this->findDeliveryDocumentOrFail($id);

            $pending = $delivery_document->pendingDetails()
                ->with(['equipment.brand', 'returnDetail'])
                ->get();

            $data = DeliveryDocumentDetailResource::collection($pending);

            return ResponseHandler::success($data, 'Equipos Pendientes de Devolución Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * RN-14: una entrega con devoluciones registradas no se puede eliminar.
     */
    public function delete(string $id)
    {
        try {
            $delivery_documents = $this->findDeliveryDocumentOrFail($id);

            if ($delivery_documents->return_documents()->exists()) {
                throw new NotAcceptable('No se puede eliminar una entrega que ya tiene devoluciones registradas');
            }

            $delivery_documents->delete();

            return ResponseHandler::success(true, 'Documento de Entrega  Eliminados Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @param  Builder<DeliveryDocument>  $query
     */
    private function applyStatusFilter(Builder $query, string $status): void
    {
        $withPending = fn (Builder $document) => $document->whereHas('details', function (Builder $detail) {
            $detail->whereDoesntHave('returnDetail');
        });

        match ($status) {
            'pendiente' => $query->whereDoesntHave('details.returnDetail'),
            'devuelto' => $query->whereDoesntHave('details', function (Builder $detail) {
                $detail->whereDoesntHave('returnDetail');
            }),
            'parcial' => $withPending($query->whereHas('details.returnDetail')),
            'activo' => $withPending($query),
        };
    }

    /**
     * @throws NotFoundError si el documento no existe.
     */
    private function findDeliveryDocumentOrFail(string $id): DeliveryDocument
    {
        $delivery_documents = DeliveryDocument::with(self::RELATIONS)->find($id);

        if (! $delivery_documents) {
            throw new NotFoundError('Documento de Entrega no encontrado');
        }

        return $delivery_documents;
    }
}
