<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::post('/employees', [EmployeeController::class, 'store']);
Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/employees/{id}', [EmployeeController::class, 'show']);
Route::put('/employees/{id}', [EmployeeController::class, 'update']);