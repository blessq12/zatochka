<?php

use App\Http\Controllers\Pricing\EstimateController;
use Illuminate\Support\Facades\Route;

Route::prefix('estimates')->group(function (): void {
    Route::post('/', [EstimateController::class, 'store']);
    Route::get('{estimateId}', [EstimateController::class, 'show']);
    Route::post('{estimateId}/calculate', [EstimateController::class, 'calculate']);
    Route::post('{estimateId}/discounts', [EstimateController::class, 'applyDiscount']);
});
