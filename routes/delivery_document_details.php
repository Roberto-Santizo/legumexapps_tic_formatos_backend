<?php

use App\Http\Controllers\DeliveryDocumentDetailController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::post('/delivery_document_details', [DeliveryDocumentDetailController::class, 'store']);
    Route::get('/delivery_document_details', [DeliveryDocumentDetailController::class, 'index']);
    Route::get('/delivery_document_details/{id}', [DeliveryDocumentDetailController::class, 'show']);
    Route::put('/delivery_document_details/{id}', [DeliveryDocumentDetailController::class, 'update']);
    Route::delete('/delivery_document_details/{id}', [DeliveryDocumentDetailController::class, 'delete']);
});
