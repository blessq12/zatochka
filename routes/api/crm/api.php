<?php

use App\Http\Controllers\CRM\ClientController;
use Illuminate\Support\Facades\Route;

Route::prefix('clients')->group(function (): void {
    Route::post('/', [ClientController::class, 'store']);
    Route::get('{clientId}', [ClientController::class, 'show']);
    Route::patch('{clientId}', [ClientController::class, 'update']);
    Route::post('{clientId}/bonuses', [ClientController::class, 'accrueBonus']);
});
