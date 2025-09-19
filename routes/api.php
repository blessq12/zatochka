<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\TelegramController;

Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook']);
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
});


Route::middleware('auth:client')->group(function () {
    Route::controller(ClientController::class)->group(function () {
        Route::get('/client/self', 'clientSelf');
        Route::get('/client/orders-get', 'clientOrdersGet');
        Route::post('/client/update', 'clientUpdate');
    });

    Route::controller(TelegramController::class)->group(function () {
        Route::post('/telegram/send-verification-code', 'telegramSendVerificationCode');
    });
});
