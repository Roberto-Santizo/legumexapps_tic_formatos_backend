<?php

use App\Http\Controllers\ReturnDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt.auth')->group(function () {
    Route::post('/return_documents', [ReturnDocumentController::class, 'store']);
    Route::get('/return_documents', [ReturnDocumentController::class, 'index']);
    Route::get('/return_documents/{id}', [ReturnDocumentController::class, 'show']);
    Route::put('/return_documents/{id}', [ReturnDocumentController::class, 'update']);
});
