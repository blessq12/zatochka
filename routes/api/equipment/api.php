<?php

use App\Http\Controllers\Equipment\EquipmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('equipment')->middleware(['auth:sanctum', 'staff'])->group(function (): void {
    Route::get('/', [EquipmentController::class, 'index']);
    Route::get('{equipmentId}/orders', [EquipmentController::class, 'orderHistory'])->whereNumber('equipmentId');
    Route::get('{equipmentId}', [EquipmentController::class, 'show'])->whereNumber('equipmentId');
});

Route::prefix('equipment')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::post('/', [EquipmentController::class, 'store']);
    Route::post('{equipmentId}/components', [EquipmentController::class, 'addComponent'])->whereNumber('equipmentId');
    Route::post('{equipmentId}/components/{componentId}/serial', [EquipmentController::class, 'registerSerial'])->whereNumber(['equipmentId', 'componentId']);
});
