<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Resources\EquipmentResource;
use App\Http\Requests\Equipment\EquipmentRequest;
use App\Models\Equipment;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $equipments = Equipment::with(['brand', 'user'])->get();
            $data = EquipmentResource::collection($equipments);

            return ResponseHandler::success($data, 'Equipos Obtenidos Correctamente', 200);
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
