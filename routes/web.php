<?php

use App\Http\Controllers\Order\PrintOrderDocumentController;
use App\Http\Controllers\SiteContent\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/print/orders/{orderId}/documents/{kind}', [PrintOrderDocumentController::class, 'page'])
        ->where('kind', 'reception_receipt|issue_act')
        ->name('documents.orders.print');

    Route::get('/print/orders/{orderId}/documents/{kind}/pdf', [PrintOrderDocumentController::class, 'pdf'])
        ->where('kind', 'reception_receipt|issue_act')
        ->name('documents.orders.print.pdf');
});

Route::controller(PublicSiteController::class)->group(function (): void {
    Route::get('/', 'home')->name('public.home');
    Route::get('/sharpening', 'sharpening')->name('public.sharpening');
    Route::get('/repair', 'repair')->name('public.repair');
    Route::get('/delivery', 'delivery')->name('public.delivery');
    Route::get('/contacts', 'contacts')->name('public.contacts');
    Route::get('/work-schedule', 'workSchedule')->name('public.work-schedule');
    Route::get('/prices', 'prices')->name('public.prices');
    Route::get('/privacy-policy', 'privacyPolicy')->name('public.privacy-policy');
    Route::get('/user-agreement', 'userAgreement')->name('public.user-agreement');
    Route::get('/usage-rules', 'usageRules')->name('public.usage-rules');
});

Route::redirect('/terms-of-service', '/user-agreement');

Route::redirect('/admin/{any?}', '/manager')->where('any', '.*');

Route::view('/client/{any?}', 'apps.client', ['title' => 'Личный кабинет — Заточка.ТСК'])
    ->where('any', '.*')
    ->name('app.client');

Route::view('/manager/{any?}', 'apps.manager', ['title' => 'Менеджер — Заточка.ТСК'])
    ->where('any', '.*')
    ->name('app.manager');

Route::view('/master/{any?}', 'apps.master', ['title' => 'Мастер — Заточка.ТСК'])
    ->where('any', '.*')
    ->name('app.master');

Route::get('/{any}', [PublicSiteController::class, 'notFound'])
    ->where('any', '.*')
    ->name('public.not-found');
