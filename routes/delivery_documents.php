<?php

use App\Http\Controllers\DeliveryDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::post('/delivery_documents', [DeliveryDocumentController::class, 'store']);
    Route::get('/delivery_documents', [DeliveryDocumentController::class, 'index']);
    Route::get('/delivery_documents/{id}', [DeliveryDocumentController::class, 'show']);
    Route::put('/delivery_documents/{id}', [DeliveryDocumentController::class, 'update']);
});
