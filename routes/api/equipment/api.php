<?php

use App\Http\Controllers\Equipment\EquipmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('equipment')->group(function (): void {
    Route::middleware(['auth:sanctum', 'master'])->group(function (): void {
        Route::get('/', [EquipmentController::class, 'index']);
        Route::get('{equipmentId}/orders', [EquipmentController::class, 'orderHistory']);
        Route::get('{equipmentId}', [EquipmentController::class, 'show']);
    });

    Route::post('/', [EquipmentController::class, 'store']);
    Route::post('{equipmentId}/components', [EquipmentController::class, 'addComponent']);
    Route::post('{equipmentId}/components/{componentId}/serial', [EquipmentController::class, 'registerSerial']);
});
