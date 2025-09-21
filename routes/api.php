<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook']);
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
});

Route::controller(OrderController::class)->group(function () {
    Route::post('/order/create', 'createOrder');
});



Route::middleware('auth:client')->group(function () {
    Route::controller(ClientController::class)->group(function () {
        Route::get('/client/self', 'clientSelf');
        Route::get('/client/orders-get', 'clientOrdersGet');
        Route::post('/client/update', 'clientUpdate');
        Route::post('/client/review', 'createReview');
    });
    Route::controller(ReviewController::class)->group(function () {
        Route::post('/review/create', 'createReview');
    });
    Route::controller(TelegramController::class)->group(function () {
        Route::post('/telegram/send-verification-code', 'telegramSendVerificationCode');
        Route::post('/telegram/verify-code', 'telegramVerifyCode');
        Route::post('/telegram/check-chat-is-exists', 'telegramCheckChatIsExists');
    });
});
