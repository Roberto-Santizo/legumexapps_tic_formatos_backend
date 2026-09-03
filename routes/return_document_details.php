<?php

use App\Http\Controllers\ReturnDocumentController;
use App\Http\Controllers\ReturnDocumentDetailController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::post('/return_document_details', [ReturnDocumentDetailController::class, 'store']);
    Route::get('/return_document_details', [ReturnDocumentDetailController::class, 'index']);
    Route::get('/return_document_details/{id}', [ReturnDocumentDetailController::class, 'show']);
    Route::put('/return_document_details/{id}', [ReturnDocumentDetailController::class, 'update']);
});
