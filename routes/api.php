<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ToolController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\OrderToolController;
use App\Http\Controllers\Api\RepairController;
use App\Http\Controllers\Api\TelegramWebhookController;
use App\Http\Controllers\Api\FaqController;

use Illuminate\Support\Facades\Route;



Route::resource('orders', OrderController::class);
Route::resource('clients', ClientController::class);
Route::resource('tools', ToolController::class);
Route::resource('notifications', NotificationController::class);
Route::resource('feedbacks', FeedbackController::class);
Route::resource('order-tools', OrderToolController::class);
Route::resource('repairs', RepairController::class);
Route::resource('faqs', FaqController::class);

// Telegram webhook
Route::controller(TelegramWebhookController::class)->group(function () {
    Route::post('/telegram/webhook', 'handleWebhook');
});
