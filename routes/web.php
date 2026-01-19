<?php

use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

// Маршруты для документов (доступны для авторизованных через Filament)
Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('api/orders/{order}')->controller(DocumentController::class)->group(function () {
        Route::get('/documents/view', 'view')->name('api.orders.documents.view');
        Route::get('/documents/pdf', 'pdf')->name('api.orders.documents.pdf');
    });
});

// SPA catch-all маршрут - возвращает layout для всех путей
// Исключаем Telescope, Filament и API документов из catch-all
Route::get('/{any?}', [MainController::class, 'index'])
    ->where('any', '^(?!telescope|cp|api).*')
    ->name('spa');
