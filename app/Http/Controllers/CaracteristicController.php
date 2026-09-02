<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\Caracteristic\CaracteristicIndexRequest;
use App\Http\Requests\Caracteristic\CaracteristicRequest;
use App\Models\Caracteristic;

class CaracteristicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CaracteristicIndexRequest $request)
    {
        try {
            $query = Caracteristic::with('equipment');

            if ($request->validated('equipmentId')) {
                $query->where('equipment_id', $request->validated('equipmentId'));
            }

            $caracteristics = $query->get();

            $data = $caracteristics->map(function ($caracteristic) {
                return [
                    'id' => $caracteristic->id,
                    'name' => $caracteristic->name,
                    'equipment' => $caracteristic->equipment->name,
                ];
            });

            return ResponseHandler::success($data, 'Características Obtenidas Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CaracteristicRequest $request)
    {
        try {
            $caracteristic = Caracteristic::create($request->validated());

            return ResponseHandler::success($caracteristic, 'Características Creadas Correctamente', 201);
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
            $caracteristic = $this->findCaracteristicOrFail($id);

            return ResponseHandler::success($caracteristic, 'Características Obtenidas Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CaracteristicRequest $request, string $id)
    {
        try {
            $caracteristic = $this->findCaracteristicOrFail($id);

            $caracteristic->update($request->validated());

            return ResponseHandler::success($caracteristic, 'Características Actualizadas Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si la característica no existe.
     */
    private function findCaracteristicOrFail(string $id): Caracteristic
    {
        $caracteristic = Caracteristic::find($id);

        if (! $caracteristic) {
            throw new NotFoundError('Característica no encontrada');
        }

        return $caracteristic;
    }
}
