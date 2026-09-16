<?php

use App\Http\Controllers\SiteContent\ManageSiteContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('site-content')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [ManageSiteContentController::class, 'show']);
    Route::put('/', [ManageSiteContentController::class, 'update']);
});
