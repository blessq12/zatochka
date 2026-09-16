<?php

use App\Http\Controllers\CRM\ClientController;
use Illuminate\Support\Facades\Route;

Route::prefix('clients')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [ClientController::class, 'index']);
    Route::post('/', [ClientController::class, 'store']);
    Route::get('{clientId}', [ClientController::class, 'show'])->whereNumber('clientId');
    Route::patch('{clientId}', [ClientController::class, 'update'])->whereNumber('clientId');
    Route::post('{clientId}/bonuses', [ClientController::class, 'accrueBonus'])->whereNumber('clientId');
});
