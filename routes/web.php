<?php

use App\Http\Controllers\Documents\PrintOrderDocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/print/orders/{orderId}/documents/{kind}', [PrintOrderDocumentController::class, 'page'])
        ->where('kind', 'reception_receipt|issue_act')
        ->name('documents.orders.print');

    Route::get('/print/orders/{orderId}/documents/{kind}/pdf', [PrintOrderDocumentController::class, 'pdf'])
        ->where('kind', 'reception_receipt|issue_act')
        ->name('documents.orders.print.pdf');
});

$publicPages = [
    '/' => ['page' => 'home', 'title' => 'Заточка.ТСК'],
    '/sharpening' => ['page' => 'sharpening', 'title' => 'Заточка'],
    '/repair' => ['page' => 'repair', 'title' => 'Ремонт'],
    '/delivery' => ['page' => 'delivery', 'title' => 'Доставка'],
    '/contacts' => ['page' => 'contacts', 'title' => 'Контакты'],
    '/work-schedule' => ['page' => 'work-schedule', 'title' => 'График работы'],
    '/prices' => ['page' => 'prices', 'title' => 'Прайс'],
    '/privacy-policy' => ['page' => 'privacy-policy', 'title' => 'Политика конфиденциальности'],
    '/user-agreement' => ['page' => 'user-agreement', 'title' => 'Пользовательское соглашение'],
    '/usage-rules' => ['page' => 'usage-rules', 'title' => 'Правила использования'],
];

foreach ($publicPages as $uri => $data) {
    Route::view($uri, 'layouts.public', $data)->name('public.'.$data['page']);
}

Route::redirect('/terms-of-service', '/user-agreement');

Route::view('/client/{any?}', 'apps.client', ['title' => 'Личный кабинет — Заточка.ТСК'])
    ->where('any', '.*')
    ->name('app.client');

Route::view('/pos/{any?}', 'apps.master', ['title' => 'POS — Заточка.ТСК'])
    ->where('any', '.*')
    ->name('app.master');

Route::view('/manager/{any?}', 'apps.manager', ['title' => 'Менеджер — Заточка.ТСК'])
    ->where('any', '.*')
    ->name('app.manager');

Route::view('/{any}', 'layouts.public', ['page' => 'not-found', 'title' => 'Страница не найдена'])
    ->where('any', '.*')
    ->name('public.not-found');
