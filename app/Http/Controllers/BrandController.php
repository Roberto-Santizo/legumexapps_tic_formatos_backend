<?php

namespace App\Http\Controllers;

use App\Errors\NotFoundError;
use App\Helpers\ResponseHandler;
use App\Http\Requests\Brand\BrandRequest;
use App\Models\Brand;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $brands = Brand::all();

            return ResponseHandler::success($brands, 'Marcas Obtenidas Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BrandRequest $request)
    {
        try {
            $brand = Brand::create($request->validated());

            return ResponseHandler::success($brand, 'Marca Creada Correctamente', 201);
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
            $brand = $this->findBrandOrFail($id);

            return ResponseHandler::success($brand, 'Marca Obtenida Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BrandRequest $request, string $id)
    {
        try {
            $brand = $this->findBrandOrFail($id);

            $brand->update($request->validated());

            return ResponseHandler::success($brand, 'Marca Actualizada Correctamente', 200);
        } catch (\Throwable $th) {
            return ResponseHandler::error($th);
        }
    }

    /**
     * @throws NotFoundError si la marca no existe.
     */
    private function findBrandOrFail(string $id): Brand
    {
        $brand = Brand::find($id);

        if (! $brand) {
            throw new NotFoundError('Marca no encontrada');
        }

        return $brand;
    }
}
