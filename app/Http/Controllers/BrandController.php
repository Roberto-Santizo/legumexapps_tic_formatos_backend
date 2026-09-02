<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         try {
            $brands = Brand::all();
            
            return ResponseHandler::success($brands, 'Marca Obtenidas Correctamente', 200);
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

            Brand::create($data);
            
            return ResponseHandler::success($data, 'Marca Creada Correctamente', 201);
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
            $brand = Brand::find($id);

            return ResponseHandler::success($brand, 'Marca Obtenida Correctamente', 200);
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
            $brand = Brand::find($id);

            $brand->update($data);

            return ResponseHandler::success($brand, 'Marca Actualizada Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }
}
