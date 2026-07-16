<?php

use App\Http\Controllers\Inventory\StockItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('stock-items')->group(function (): void {
    Route::middleware(['auth:sanctum', 'master'])->group(function (): void {
        Route::get('/', [StockItemController::class, 'index']);
        Route::get('{stockItemId}', [StockItemController::class, 'show']);
    });

    Route::post('/', [StockItemController::class, 'store']);
    Route::post('{stockItemId}/receive', [StockItemController::class, 'receive']);
    Route::post('{stockItemId}/write-off', [StockItemController::class, 'writeOff']);
    Route::post('{stockItemId}/change', [StockItemController::class, 'change']);
});
