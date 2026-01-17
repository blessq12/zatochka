<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PosController;
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

Route::controller(\App\Http\Controllers\Api\TelegramController::class)->group(function () {
    Route::post('/telegram/webhook', 'webhook');
});

Route::middleware('auth:client')->group(function () {
    Route::controller(\App\Http\Controllers\Api\TelegramController::class)->group(function () {
        Route::post('/telegram/send-verification-code', 'sendVerificationCode');
        Route::post('/telegram/verify-code', 'verifyCode');
        Route::post('/telegram/check-chat-is-exists', 'checkChatExists');
    });
});



Route::middleware('auth:client')->group(function () {
    Route::controller(ClientController::class)->group(function () {
        Route::get('/client/self', 'clientSelf');
        Route::get('/client/orders-get', 'clientOrdersGet');
        Route::post('/client/update', 'clientUpdate');
    });
});

// POS API для мастеров
Route::prefix('pos')->controller(PosController::class)->group(function () {
    Route::post('/login', 'login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('pos')->controller(PosController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/me', 'me');
        Route::post('/profile/update', 'updateProfile');
        Route::get('/orders', 'orders');
        Route::get('/orders/count', 'ordersCount'); // Должен быть ПЕРЕД /orders/{id}
        Route::get('/orders/{id}', 'order');
        Route::get('/warehouse/items', 'warehouseItems');
    });
});
