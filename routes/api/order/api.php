<?php

use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\SharpeningToolTypeCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('sharpening-tool-types', [SharpeningToolTypeCatalogController::class, 'index']);

Route::prefix('orders')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('{orderId}', [OrderController::class, 'show']);
    Route::post('{orderId}/assign-master', [OrderController::class, 'assignMaster']);
    Route::post('{orderId}/cancel', [OrderController::class, 'cancel']);
    Route::post('{orderId}/issue', [OrderController::class, 'issue']);
});
