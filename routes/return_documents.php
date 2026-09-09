<?php

use App\Http\Controllers\ReturnDocumentController;
use Illuminate\Support\Facades\Route;

// RN-07: emitir o corregir una devolución queda restringido a `admin`; las
// lecturas quedan abiertas a cualquier usuario autenticado.
Route::middleware(['jwt.auth', 'admin'])->group(function () {
    Route::post('/return_documents', [ReturnDocumentController::class, 'store']);
    Route::put('/return_documents/{id}', [ReturnDocumentController::class, 'update']);
});

Route::middleware('jwt.auth')->group(function () {
    Route::get('/return_documents', [ReturnDocumentController::class, 'index']);
    Route::get('/return_documents/{id}', [ReturnDocumentController::class, 'show']);
});
