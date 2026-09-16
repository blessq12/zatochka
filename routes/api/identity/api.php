<?php

use App\Http\Controllers\Identity\StaffUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('staff-users')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [StaffUserController::class, 'index']);
    Route::post('/', [StaffUserController::class, 'store']);
    Route::get('{userId}', [StaffUserController::class, 'show'])->whereNumber('userId');
    Route::patch('{userId}', [StaffUserController::class, 'update'])->whereNumber('userId');
    Route::post('{userId}/password', [StaffUserController::class, 'changePassword'])->whereNumber('userId');
    Route::delete('{userId}', [StaffUserController::class, 'destroy'])->whereNumber('userId');
});
