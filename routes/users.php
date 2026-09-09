<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// La administración de usuarios está reservada al rol `admin`.
Route::middleware(['jwt.auth', 'admin'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
});
