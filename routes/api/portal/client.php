<?php

use App\Http\Controllers\CRM\Portal\ClientPortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('client')->middleware(['auth:sanctum', 'client'])->group(function (): void {
    Route::get('profile', [ClientPortalController::class, 'profile']);
    Route::patch('profile', [ClientPortalController::class, 'updateProfile']);
    Route::post('password', [ClientPortalController::class, 'setPassword']);
    Route::get('orders/active', [ClientPortalController::class, 'activeOrders']);
    Route::get('orders/history', [ClientPortalController::class, 'historyOrders']);
    Route::post('orders/{orderId}/review', [ClientPortalController::class, 'submitReview']);
});
