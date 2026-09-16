<?php

use App\Http\Controllers\SiteContent\ManageLegalDocumentController;
use App\Http\Controllers\SiteContent\ManageSiteContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('site-content')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [ManageSiteContentController::class, 'show']);
    Route::put('/', [ManageSiteContentController::class, 'update']);
});

Route::prefix('legal-documents')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [ManageLegalDocumentController::class, 'index']);
    Route::get('{type}', [ManageLegalDocumentController::class, 'show']);
    Route::put('{type}', [ManageLegalDocumentController::class, 'update']);
});
