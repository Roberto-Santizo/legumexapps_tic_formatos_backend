<?php

use App\Http\Controllers\EquipmentController;
use Illuminate\Support\Facades\Route;

Route::post('/equipments', [EquipmentController::class, 'store'])->middleware(['jwt.auth']);
Route::get('/equipments', [EquipmentController::class, 'index'])->middleware(['jwt.auth']);
Route::get('/equipments/{id}', [EquipmentController::class, 'show'])->middleware(['jwt.auth']);
Route::put('/equipments/{id}', [EquipmentController::class, 'update'])->middleware(['jwt.auth']);