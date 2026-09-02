<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $departments = Department::all();

            return ResponseHandler::success($departments, 'Departamentos Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate(['name' => 'required']);

            Department::create($data);
            
            return ResponseHandler::success($data, 'Departamento Creado Correctamente', 201);
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
            $department = Department::find($id);

            return ResponseHandler::success($department, 'Departamento Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = $request->validate(['name' => 'required']);
            $department = Department::find($id);

            $department->update($data);

            return ResponseHandler::success($department, 'Departamento Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }
}
