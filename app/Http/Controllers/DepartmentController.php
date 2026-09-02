<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\Department\DepartmentRequest;
use App\Models\Department;

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
    public function store(DepartmentRequest $request)
    {
        try {
            $department = Department::create($request->validated());

            return ResponseHandler::success($department, 'Departamento Creado Correctamente', 201);
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
            $department = $this->findDepartmentOrFail($id);

            return ResponseHandler::success($department, 'Departamento Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartmentRequest $request, string $id)
    {
        try {
            $department = $this->findDepartmentOrFail($id);

            $department->update($request->validated());

            return ResponseHandler::success($department, 'Departamento Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el departamento no existe.
     */
    private function findDepartmentOrFail(string $id): Department
    {
        $department = Department::find($id);

        if (! $department) {
            throw new NotFoundError('Departamento no encontrado');
        }

        return $department;
    }
}
