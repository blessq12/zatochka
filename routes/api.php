<?php

use App\Http\Controllers\Api\BootstrapController;
use App\Http\Controllers\Api\ClientAccountController;
use App\Http\Controllers\Api\ClientAuthController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Pos\PosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('bootstrap', [BootstrapController::class, 'show']);

Route::post('leads', [LeadController::class, 'store']);

Route::prefix('auth')->group(function (): void {
    Route::post('register', [ClientAuthController::class, 'register']);
    Route::post('login', [ClientAuthController::class, 'login']);
});

Route::prefix('client')->middleware('auth:sanctum')->group(function (): void {
    Route::get('profile', [ClientAccountController::class, 'profile']);
    Route::patch('profile', [ClientAccountController::class, 'updateProfile']);
    Route::post('password', [ClientAuthController::class, 'setPassword']);

    Route::get('orders/active', [ClientAccountController::class, 'activeOrders']);
    Route::get('orders/history', [ClientAccountController::class, 'orderHistory']);
    Route::get('orders/{orderId}', [ClientAccountController::class, 'orderDetail']);
    Route::post('orders/{orderId}/review', [ClientAccountController::class, 'submitReview']);
});

Route::prefix('pos')->group(function (): void {
    Route::post('login', [PosController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('dashboard', [PosController::class, 'dashboard']);
        Route::get('orders/counts', [PosController::class, 'counts']);
        Route::get('orders', [PosController::class, 'index']);
        Route::get('orders/{orderId}', [PosController::class, 'show']);
        Route::post('orders/{orderId}/take-to-work', [PosController::class, 'takeToWork']);
        Route::post('orders/{orderId}/waiting-parts', [PosController::class, 'markWaitingForParts']);
        Route::post('orders/{orderId}/resume', [PosController::class, 'resume']);
        Route::post('orders/{orderId}/works', [PosController::class, 'addWork']);
        Route::delete('orders/{orderId}/works', [PosController::class, 'removeWork']);
        Route::patch('orders/{orderId}/internal-notes', [PosController::class, 'updateInternalNotes']);
        Route::post('orders/{orderId}/mark-ready', [PosController::class, 'markReady']);

        Route::get('warehouse/items', [PosController::class, 'searchWarehouseItems']);
        Route::get('equipment', [PosController::class, 'searchEquipment']);
        Route::get('equipment/{equipmentId}/orders', [PosController::class, 'equipmentOrderHistory']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
