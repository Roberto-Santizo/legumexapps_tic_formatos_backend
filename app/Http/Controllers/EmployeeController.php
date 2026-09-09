<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\EmployeeResource;
use App\Models\DeliveryDocumentDetail;
use App\Errors\NotAcceptable;
use Illuminate\Http\Request;
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

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required',
                'code' => 'required',
                'department_id' => ['required', 'exists:departments,id'],
            ]);

            Employee::create($data);

            return ResponseHandler::success($data, 'Empleado Creado Correctamente', 201);
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

    public function update(Request $request, string $id)
    {
        try {
            $data = $request->validate([
                'name' => 'required',
                'code' => 'required',
                'department_id' => ['required', 'exists:departments,id'],
            ]);

            $employee = $this->findEmployeeOrFail($id);

            $employee->update($data);

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
