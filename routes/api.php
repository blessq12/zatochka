<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;


Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
});


Route::middleware('auth:client')->group(function () {
    Route::get('/client/self', [ClientController::class, 'clientSelf']);
    Route::get('/client/orders-get', [ClientController::class, 'clientOrdersGet']);
});
