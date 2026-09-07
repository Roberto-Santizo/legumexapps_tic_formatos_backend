<?php

use App\Http\Controllers\EquipmentController;
use Illuminate\Support\Facades\Route;

Route::post('/equipments', [EquipmentController::class, 'store'])->middleware(['jwt.auth']);
Route::get('/equipments', [EquipmentController::class, 'index'])->middleware(['jwt.auth']);
// Debe declararse antes de /equipments/{id} para que "available" no se tome como id.
Route::get('/equipments/available', [EquipmentController::class, 'available'])->middleware(['jwt.auth']);
Route::get('/equipments/{id}', [EquipmentController::class, 'show'])->middleware(['jwt.auth']);
Route::get('/equipments/{id}/history', [EquipmentController::class, 'history'])->middleware(['jwt.auth']);
Route::put('/equipments/{id}', [EquipmentController::class, 'update'])->middleware(['jwt.auth']);
