<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\MainController;

Route::controller(MainController::class)
    ->prefix('')
    ->group(function () {
        Route::get('/', 'index')->name('home');
    });
