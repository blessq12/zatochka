<?php

use App\Http\Controllers\Order\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->group(function (): void {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('{orderId}', [OrderController::class, 'show']);
    Route::post('{orderId}/reception', [OrderController::class, 'completeReception']);
    Route::post('{orderId}/cancel', [OrderController::class, 'cancel']);
    Route::post('{orderId}/close', [OrderController::class, 'close']);
    Route::post('{orderId}/issue', [OrderController::class, 'issue']);
});
