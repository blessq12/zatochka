<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\MainController;
use App\Http\Controllers\FilamentAuthController;

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

// Тестовая страница аутентификации клиентов
Route::get('/client-auth-test', function () {
    return view('pages.client-auth-test');
})->name('client-auth-test');

// Личный кабинет клиента
Route::get('/dashboard', function () {
    return view('pages.client-dashboard');
})->name('client-dashboard');


Route::controller(FilamentAuthController::class)->group(function () {
    Route::get('/crm', 'login')->name('crm.login');
    Route::post('/crm', 'authenticate')->name('crm.authenticate');
    Route::get('/logout', 'logout')->name('crm.logout');
});
