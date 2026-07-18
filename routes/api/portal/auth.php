<?php

use App\Http\Controllers\CRM\Portal\ClientPortalAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [ClientPortalAuthController::class, 'register']);
    Route::post('login', [ClientPortalAuthController::class, 'login']);
    Route::post('logout', [ClientPortalAuthController::class, 'logout'])
        ->middleware(['auth:sanctum', 'client']);
});
