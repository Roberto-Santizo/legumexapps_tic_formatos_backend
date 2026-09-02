<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index()
    {
        try {
            $equipments = Equipment::all();

            $data = $equipments->map(function ($equipment) {
                return[
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'brand'=> $equipment->brand->name,
                    'registeredBy'=> $equipment->user->name,
                    ];
            });

            return ResponseHandler::success($data, 'Equipos Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required',
                'brand_id' => ['required', 'exists:brands,id'],
                'model' => 'required',
                'serie'=> 'required',
                'original'=> 'required',
                'is_used'=> 'required',
                'type'=> 'required'
            ]);

            $data['registerdBy'] = auth()->user()->id;

            Equipment::create($data);
            
            return ResponseHandler::success($data, 'Equipo Creado Correctamente', 201);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }
    public function show(string $id)
    {
        try {
            $equipment = Equipment::find($id);

            return ResponseHandler::success($equipment, 'Equipo Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $data = $request->validate([
                'name' => 'required',
                'brand_id' => ['required', 'exists:brands,id'], 
                'model' => 'required',
                'serie'=> 'required',
                'original'=> 'required',
                'is_used'=> 'required',
                'type'=> 'required'
            ]);

            $data['registerdBy'] = auth()->user()->id;

            $equipment = Equipment::find($id);

            $equipment->update($data);

            return ResponseHandler::success($equipment, 'Equipo Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

}
