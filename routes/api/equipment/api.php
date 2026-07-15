<?php

use App\Http\Controllers\Equipment\EquipmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('equipment')->group(function (): void {
    Route::post('/', [EquipmentController::class, 'store']);
    Route::get('{equipmentId}', [EquipmentController::class, 'show']);
    Route::post('{equipmentId}/components', [EquipmentController::class, 'addComponent']);
    Route::post('{equipmentId}/components/{componentId}/serial', [EquipmentController::class, 'registerSerial']);
});
