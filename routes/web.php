<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

// SPA catch-all маршрут - возвращает layout для всех путей
// Исключаем Telescope и Filament из catch-all
Route::get('/{any?}', [MainController::class, 'index'])
    ->where('any', '^(?!telescope|cp).*')
    ->name('spa');
