<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\Caracteristic;
use Illuminate\Http\Request;

class CaracteristicController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Caracteristic::query();

            if($request->query('equipmentId')){
                $query->where('equipment_id', $request->query('equipmentId'));
            }

            $caracteristics = $query->get();

            $data = $caracteristics->map(function ($caracteristic) {
                return[
                    'id' => $caracteristic->id,
                    'name' => $caracteristic->name,
                    'equipment'=> $caracteristic->equipment->name,
                    ];
            });

            return ResponseHandler::success($data, 'Características Obtenidas Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required',
                'description' => 'required',
                'equipment_id' => ['required', 'exists:equipments,id'],
            ]);

            $data['registerdBy'] = auth()->user()->id;

            Caracteristic::create($data);
            
            return ResponseHandler::success($data, 'Características Creadas Correctamente', 201);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }
    public function show(string $id)
    {
        try {
            $caracteristic = Caracteristic::find($id);

            return ResponseHandler::success($caracteristic, 'Características Obtenidas Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $data = $request->validate([
                'name' => 'required',
                'description' => 'required',
                'equipment_id' => ['required', 'exists:equipments,id']
            ]);

            $data['registerdBy'] = auth()->user()->id;

            $caracteristic = Caracteristic::find($id);

            $caracteristic->update($data);

            return ResponseHandler::success($caracteristic, 'Características Actualizadas Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

}
