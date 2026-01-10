<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PriceController;
use Illuminate\Support\Facades\Route;
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
});

Route::controller(OrderController::class)->group(function () {
    Route::post('/order/create', 'createOrder');
});

Route::controller(PriceController::class)->group(function () {
    Route::get('/prices/sharpening', 'sharpening');
    Route::get('/prices/repair', 'repair');
    Route::get('/prices/all', 'all');
});



Route::middleware('auth:client')->group(function () {
    Route::controller(ClientController::class)->group(function () {
        Route::get('/client/self', 'clientSelf');
        Route::get('/client/orders-get', 'clientOrdersGet');
        Route::post('/client/update', 'clientUpdate');
    });
});
