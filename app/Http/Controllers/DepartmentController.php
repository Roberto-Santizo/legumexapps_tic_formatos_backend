<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\Department\DepartmentIndexRequest;
use App\Http\Requests\Department\DepartmentRequest;
use App\Http\Resources\PaginatedDepartmentResource;
use App\Models\Department;

class DepartmentController extends Controller
{
    /**
     * Listado de departamentos. Se pagina sólo cuando llega `limit`.
     */
    public function index(DepartmentIndexRequest $request)
    {
        try {
            $data = $this->paginateOrAll(Department::query(), $request, PaginatedDepartmentResource::class);

            return ResponseHandler::success($data, 'Departamentos Obtenidos Correctamente', 200);
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
