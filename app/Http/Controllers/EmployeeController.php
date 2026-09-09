<?php

namespace App\Http\Controllers;

use App\Errors\NotAcceptable;
use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\Employee\EmployeeRequest;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\EmployeeResource;
use App\Models\DeliveryDocumentDetail;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

class EmployeeController extends Controller
{
    public function index()
    {
        try {
            $employees = Employee::with('department')->get();
            $data = EmployeeResource::collection($employees);

            return ResponseHandler::success($data, 'Empleados Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function store(EmployeeRequest $request)
    {
        try {
            $employee = Employee::create($request->validated());

            return ResponseHandler::success($employee, 'Empleado Creado Correctamente', 201);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function show(string $id)
    {
        try {
            $employee = $this->findEmployeeOrFail($id);

            return ResponseHandler::success($employee, 'Empleado Obtenido Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    public function update(EmployeeRequest $request, string $id)
    {
        try {
            $employee = $this->findEmployeeOrFail($id);

            $employee->update($request->validated());

            return ResponseHandler::success($employee, 'Empleado Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Equipos que el empleado tiene asignados en este momento: detalles de
     * entrega suyos que todavía no tienen devolución.
     */
    public function equipments(string $id)
    {
        try {
            $employee = $this->findEmployeeOrFail($id);

            $assignments = DeliveryDocumentDetail::with([
                'equipment.brand',
                'delivery_documents.employee.department',
                'returnDetail.return_document',
            ])
                ->whereHas('delivery_documents', function ($document) use ($employee) {
                    $document->where('employee_id', $employee->id);
                })
                ->whereDoesntHave('returnDetail')
                ->orderByDesc('id')
                ->get();

            $data = AssignmentResource::collection($assignments);

            return ResponseHandler::success($data, 'Equipos Asignados Obtenidos Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * RN-24: no se elimina un empleado con equipo asignado.
     */
    public function delete(string $id)
    {
        try {
            $employee = $this->findEmployeeOrFail($id);

            $tieneEquipo = DeliveryDocumentDetail::query()
                ->whereDoesntHave('returnDetail')
                ->whereHas('delivery_documents', function (Builder $document) use ($employee) {
                    $document->where('employee_id', $employee->id);
                })
                ->exists();

            if ($tieneEquipo) {
                throw new NotAcceptable('No se puede eliminar un empleado que tiene equipo asignado');
            }

            $employee->delete();

            return ResponseHandler::success($employee, 'Empleado Eliminado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si el empleado no existe.
     */
    private function findEmployeeOrFail(string $id): Employee
    {
        $employee = Employee::find($id);

        if (! $employee) {
            throw new NotFoundError('Empleado no encontrado');
        }

        return $employee;
    }
}
