<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::post('/employees', [EmployeeController::class, 'store'])->middleware(['jwt.auth']);
Route::get('/employees', [EmployeeController::class, 'index'])->middleware(['jwt.auth']);
Route::get('/employees/{id}', [EmployeeController::class, 'show'])->middleware(['jwt.auth']);
Route::put('/employees/{id}', [EmployeeController::class, 'update'])->middleware(['jwt.auth']);
