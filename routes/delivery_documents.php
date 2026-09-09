<?php

use App\Http\Controllers\DeliveryDocumentController;
use Illuminate\Support\Facades\Route;

// RN-07: emitir, corregir o eliminar una entrega queda restringido a `admin`;
// las lecturas quedan abiertas a cualquier usuario autenticado.
Route::middleware(['jwt.auth', 'admin'])->group(function () {
    Route::post('/delivery_documents', [DeliveryDocumentController::class, 'store']);
    Route::put('/delivery_documents/{id}', [DeliveryDocumentController::class, 'update']);
    Route::delete('/delivery_documents/{id}', [DeliveryDocumentController::class, 'delete']);
});

Route::middleware('jwt.auth')->group(function () {
    Route::get('/delivery_documents', [DeliveryDocumentController::class, 'index']);
    Route::get('/delivery_documents/{id}', [DeliveryDocumentController::class, 'show']);
    Route::get('/delivery_documents/{id}/pending_items', [DeliveryDocumentController::class, 'pendingItems']);
});
