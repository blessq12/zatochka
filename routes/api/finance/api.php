<?php

use App\Http\Controllers\Finance\CashOperationController;
use App\Http\Controllers\Finance\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('payments')->group(function (): void {
    Route::post('/', [PaymentController::class, 'store']);
    Route::get('{paymentId}', [PaymentController::class, 'show']);
    Route::post('{paymentId}/refunds', [PaymentController::class, 'refund']);
});

Route::post('cash-operations', [CashOperationController::class, 'store']);
