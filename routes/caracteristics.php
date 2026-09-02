<?php

use App\Http\Controllers\CaracteristicController;
use App\Http\Controllers\EquipmentController;
use Illuminate\Support\Facades\Route;

Route::post('/caracteristics', [CaracteristicController::class, 'store'])->middleware(['jwt.auth']);
Route::get('/caracteristics', [CaracteristicController::class, 'index'])->middleware(['jwt.auth']);
Route::get('/caracteristics/{id}', [CaracteristicController::class, 'show'])->middleware(['jwt.auth']);
Route::put('/caracteristics/{id}', [CaracteristicController::class, 'update'])->middleware(['jwt.auth']);