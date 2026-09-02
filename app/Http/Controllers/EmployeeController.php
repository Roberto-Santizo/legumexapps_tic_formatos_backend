<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        try {
            $employees = Employee::all();

            $data = $employees->map(function ($employee) { 
                return [
                    'id' => $employee->id,
                    'code' => $employee->code,
                    'name' => $employee->name,
                    'department' => $employee->department->name
                ];
            });

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
                'department_id' => ['required', 'exists:departments,id']   
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
            $employee = Employee::find($id);

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
                'department_id' => ['required', 'exists:departments,id']
                ]);
            $employee = Employee::find($id);

            $employee->update($data);

            return ResponseHandler::success($employee, 'Empleado Actualizado Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }
}
