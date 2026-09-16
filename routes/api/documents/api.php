<?php

use App\Http\Controllers\Documents\ManageLegalDocumentController;
use Illuminate\Support\Facades\Route;

Route::prefix('legal-documents')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [ManageLegalDocumentController::class, 'index']);
    Route::get('{type}', [ManageLegalDocumentController::class, 'show']);
    Route::put('{type}', [ManageLegalDocumentController::class, 'update']);
});
