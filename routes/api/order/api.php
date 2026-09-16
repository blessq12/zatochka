<?php

use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\ReviewController;
use App\Http\Controllers\Order\SharpeningToolTypeCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('sharpening-tool-types', [SharpeningToolTypeCatalogController::class, 'index']);

Route::prefix('orders')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('{orderId}', [OrderController::class, 'show']);
    Route::get('{orderId}/container', [OrderController::class, 'container']);
    Route::post('{orderId}/reception', [OrderController::class, 'completeReception']);
    Route::post('{orderId}/cancel', [OrderController::class, 'cancel']);
    Route::post('{orderId}/close', [OrderController::class, 'close']);
    Route::post('{orderId}/issue', [OrderController::class, 'issue']);
});

Route::prefix('reviews')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [ReviewController::class, 'index']);
    Route::get('{reviewId}', [ReviewController::class, 'show'])->whereNumber('reviewId');
    Route::post('{reviewId}/publish', [ReviewController::class, 'publish'])->whereNumber('reviewId');
    Route::post('{reviewId}/reject', [ReviewController::class, 'reject'])->whereNumber('reviewId');
    Route::post('{reviewId}/hide', [ReviewController::class, 'hide'])->whereNumber('reviewId');
    Route::post('{reviewId}/restore', [ReviewController::class, 'restore'])->whereNumber('reviewId');
    Route::post('{reviewId}/reply', [ReviewController::class, 'reply'])->whereNumber('reviewId');
    Route::delete('{reviewId}', [ReviewController::class, 'destroy'])->whereNumber('reviewId');
});

Route::middleware(['auth:sanctum', 'master'])->group(function (): void {
    Route::post('orders/{orderId}/items/{orderItemId}/reject-units', [OrderController::class, 'rejectItemUnits']);
});
