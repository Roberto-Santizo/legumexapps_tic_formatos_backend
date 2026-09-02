<?php

use App\Http\Controllers\BrandController;
use Illuminate\Support\Facades\Route;

Route::post('/brands', [BrandController::class, 'store']);
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{id}', [BrandController::class, 'show']);
Route::put('/brands/{id}', [BrandController::class, 'update']);