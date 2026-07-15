<?php

use App\Http\Controllers\Delivery\DeliveryRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('delivery-requests')->group(function (): void {
    Route::post('/', [DeliveryRequestController::class, 'store']);
    Route::get('{deliveryRequestId}', [DeliveryRequestController::class, 'show']);
    Route::post('{deliveryRequestId}/assign-courier', [DeliveryRequestController::class, 'assignCourier']);
    Route::post('{deliveryRequestId}/collect', [DeliveryRequestController::class, 'collect']);
    Route::post('{deliveryRequestId}/deliver', [DeliveryRequestController::class, 'deliver']);
});
