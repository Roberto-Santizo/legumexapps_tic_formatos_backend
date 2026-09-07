<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\Equipment\EquipmentAvailableRequest;
use App\Http\Requests\Equipment\EquipmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\EquipmentResource;
use App\Models\DeliveryDocumentDetail;
use App\Models\Equipment;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $equipments = Equipment::with(['brand', 'user', 'deliveryDetail.returnDetail'])->get();
            $data = EquipmentResource::collection($equipments);

            return ResponseHandler::success($data, 'Equipos Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Equipos sin entrega activa, es decir, los que se pueden incluir en una
     * nueva entrega. Acepta los filtros `type` y `search`.
     */
    public function available(EquipmentAvailableRequest $request)
    {
        try {
            $query = Equipment::with(['brand', 'user', 'deliveryDetail.returnDetail'])->available();

            if ($request->validated('type')) {
                $query->where('type', $request->validated('type'));
            }

            if ($request->validated('search')) {
                $search = $request->validated('search');

                $query->where(function ($equipment) use ($search) {
                    $equipment->where('name', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('serie', 'like', "%{$search}%");
                });
            }

            $data = EquipmentResource::collection($query->get());

            return ResponseHandler::success($data, 'Equipos Disponibles Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Historial de asignaciones del equipo: cada entrega en la que se incluyó y
     * su devolución cuando ya se registró.
     */
    public function history(string $id)
    {
        try {
            $equipment = $this->findEquipmentOrFail($id);

            $assignments = DeliveryDocumentDetail::with([
                'equipment.brand',
                'delivery_documents.employee.department',
                'returnDetail.return_document',
            ])
                ->where('equipment_id', $equipment->id)
                ->orderByDesc('id')
                ->get();

            $data = AssignmentResource::collection($assignments);

            return ResponseHandler::success($data, 'Historial del Equipo Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EquipmentRequest $request)
    {
        try {
            $equipment = Equipment::create([
                ...$request->validated(),
                'registerdBy' => auth()->user()->id,
            ]);

            return ResponseHandler::success($equipment, 'Equipo Creado Correctamente', 201);
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
            $equipment = $this->findEquipmentOrFail($id);

            return ResponseHandler::success($equipment, 'Equipo Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EquipmentRequest $request, string $id)
    {
        try {
            $equipment = $this->findEquipmentOrFail($id);

            $equipment->update($request->validated());

            return ResponseHandler::success($equipment, 'Equipo Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el equipo no existe.
     */
    private function findEquipmentOrFail(string $id): Equipment
    {
        $equipment = Equipment::find($id);

        if (! $equipment) {
            throw new NotFoundError('Equipo no encontrado');
        }

        return $equipment;
    }
}
