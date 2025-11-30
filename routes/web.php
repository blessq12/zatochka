<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

// SPA catch-all маршрут - возвращает layout для всех путей
Route::get('/{any?}', [MainController::class, 'index'])
    ->where('any', '.*')
    ->name('spa');
