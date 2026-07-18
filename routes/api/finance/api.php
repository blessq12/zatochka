<?php

use App\Http\Controllers\Finance\CashOperationController;
use App\Http\Controllers\Finance\PaymentController;
use App\Http\Controllers\Finance\PaymentMethodCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('payment-methods', [PaymentMethodCatalogController::class, 'index']);

Route::prefix('payments')->group(function (): void {
    Route::post('/', [PaymentController::class, 'store']);
    Route::get('{paymentId}', [PaymentController::class, 'show']);
    Route::post('{paymentId}/refunds', [PaymentController::class, 'refund']);
});

Route::post('cash-operations', [CashOperationController::class, 'store']);
