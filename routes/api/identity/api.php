<?php

use App\Http\Controllers\Identity\EmployeeController;
use App\Http\Controllers\Identity\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('employees')->group(function (): void {
    Route::post('/', [EmployeeController::class, 'store']);
    Route::get('{employeeId}', [EmployeeController::class, 'show']);
    Route::post('{employeeId}/roles', [EmployeeController::class, 'assignRole']);
});

Route::prefix('roles')->group(function (): void {
    Route::post('/', [RoleController::class, 'store']);
    Route::post('{roleId}/permissions', [RoleController::class, 'grantPermission']);
});
