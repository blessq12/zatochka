<?php

use App\Http\Controllers\Equipment\EquipmentTypeCatalogController;
use App\Http\Controllers\Order\PublicOrderController;
use App\Http\Controllers\Order\SharpeningToolTypeCatalogController;
use Illuminate\Support\Facades\Route;

Route::post('public/orders', [PublicOrderController::class, 'store']);
Route::get('public/sharpening-tool-types', [SharpeningToolTypeCatalogController::class, 'index']);
Route::get('public/equipment-types', [EquipmentTypeCatalogController::class, 'index']);
