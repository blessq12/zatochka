<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\MainController;

Route::controller(MainController::class)
    ->prefix('')
    ->group(function () {
        Route::get('/', 'index')->name('home');
        Route::get('/sharpening', 'sharpening')->name('sharpening');
        Route::get('/repair', 'repair')->name('repair');
        Route::get('/delivery', 'delivery')->name('delivery');
        Route::get('/contacts', 'contacts')->name('contacts');
    });
