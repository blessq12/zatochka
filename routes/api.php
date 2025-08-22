<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ClientAuthController;
use App\Http\Controllers\Api\ClientPasswordResetController;
use App\Http\Controllers\Api\ClientTelegramVerificationController;
use App\Http\Controllers\Api\ToolController;
use App\Http\Controllers\Api\NotificationController;

use App\Http\Controllers\Api\OrderToolController;
use App\Http\Controllers\Api\RepairController;
use App\Http\Controllers\Api\TelegramWebhookController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\ReviewController;

use Illuminate\Support\Facades\Route;



// Маршруты для гостей (без аутентификации)
Route::post('client/register', [ClientAuthController::class, 'register']);
Route::post('client/login', [ClientAuthController::class, 'login']);

// Маршруты для сброса пароля
Route::post('client/forgot-password', [ClientPasswordResetController::class, 'sendResetLink']);
Route::post('client/reset-password', [ClientPasswordResetController::class, 'reset']);

// Маршруты для Telegram верификации
Route::post('client/telegram/send-code', [ClientTelegramVerificationController::class, 'sendVerificationCode']);
Route::post('client/telegram/verify-code', [ClientTelegramVerificationController::class, 'verifyCode']);
Route::get('client/telegram/status', [ClientTelegramVerificationController::class, 'checkVerificationStatus']);
Route::put('client/telegram/update', [ClientTelegramVerificationController::class, 'updateTelegram']);

// Защищенные маршруты (требуют аутентификации)
Route::middleware('auth:sanctum')->group(function () {
    // Аутентификация клиентов
    Route::post('client/logout', [ClientAuthController::class, 'logout']);
    Route::get('client/profile', [ClientAuthController::class, 'profile']);
    Route::put('client/profile', [ClientAuthController::class, 'updateProfile']);
    Route::put('client/change-password', [ClientAuthController::class, 'changePassword']);
    Route::get('client/check-token', [ClientAuthController::class, 'checkToken']);

    // Маршруты, требующие верификации Telegram
    Route::middleware('client.telegram.verified')->group(function () {
        // Здесь будут маршруты, требующие верификации
        // Например: создание заказов, доступ к премиум функциям и т.д.
    });
});

Route::resource('orders', OrderController::class);
Route::resource('clients', ClientController::class);
Route::resource('tools', ToolController::class);
Route::resource('notifications', NotificationController::class);
Route::resource('order-tools', OrderToolController::class);
Route::resource('repairs', RepairController::class);
Route::resource('faqs', FaqController::class);
Route::resource('reviews', ReviewController::class)->only(['index', 'store', 'show']);
Route::get('reviews/stats', [ReviewController::class, 'stats']);

// Telegram webhook
Route::controller(TelegramWebhookController::class)->group(function () {
    Route::post('/telegram/webhook', 'handleWebhook');
    Route::post('/telegram/set-webhook', 'setWebhook');
    Route::get('/telegram/webhook-info', 'getWebhookInfo');
    Route::delete('/telegram/webhook', 'deleteWebhook');
    Route::post('/telegram/test-message', 'sendTestMessage');
});
