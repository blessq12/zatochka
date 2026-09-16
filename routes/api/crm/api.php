<?php

use App\Http\Controllers\CRM\ClientController;
use App\Http\Controllers\CRM\StaffUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('clients')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [ClientController::class, 'index']);
    Route::post('/', [ClientController::class, 'store']);
    Route::get('{clientId}', [ClientController::class, 'show'])->whereNumber('clientId');
    Route::patch('{clientId}', [ClientController::class, 'update'])->whereNumber('clientId');
});

Route::prefix('staff-users')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [StaffUserController::class, 'index']);
    Route::post('/', [StaffUserController::class, 'store']);
    Route::get('{userId}', [StaffUserController::class, 'show'])->whereNumber('userId');
    Route::patch('{userId}', [StaffUserController::class, 'update'])->whereNumber('userId');
    Route::post('{userId}/password', [StaffUserController::class, 'changePassword'])->whereNumber('userId');
    Route::delete('{userId}', [StaffUserController::class, 'destroy'])->whereNumber('userId');
});
