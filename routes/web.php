<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthControllerFilament;
//
use App\Http\Controllers\MainController;
use App\Http\Controllers\ClientController;

Route::controller(MainController::class)
    ->prefix('')
    ->group(function () {
        Route::get('/', 'index')->name('home');
        Route::get('/sharpening', 'sharpening')->name('sharpening');
        Route::get('/repair', 'repair')->name('repair');
        Route::get('/delivery', 'delivery')->name('delivery');
        Route::get('/contacts', 'contacts')->name('contacts');
        Route::get('/privacy-policy', 'privacyPolicy')->name('privacy-policy');
        Route::get('/terms-of-service', 'termsOfService')->name('terms-of-service');
        Route::get('/help', 'help')->name('help');
    });

Route::controller(ClientController::class)
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
    });

Route::controller(AuthControllerFilament::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'loginPost')->name('login.post');
});
