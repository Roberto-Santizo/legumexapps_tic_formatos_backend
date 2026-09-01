<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/check-status', [AuthController::class, 'checkstatus'])->middleware('jwt.auth');

Route::post('/register', [AuthController::class, 'register'])->middleware(['jwt.auth', 'admin']);
