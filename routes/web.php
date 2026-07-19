<?php

use App\Http\Controllers\Documents\PrintOrderDocumentController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/print/orders/{orderId}/documents/{kind}', [PrintOrderDocumentController::class, 'page'])
        ->where('kind', 'reception_receipt|issue_act')
        ->name('documents.orders.print');

    Route::get('/print/orders/{orderId}/documents/{kind}/pdf', [PrintOrderDocumentController::class, 'pdf'])
        ->where('kind', 'reception_receipt|issue_act')
        ->name('documents.orders.print.pdf');
});

Route::get('/{any?}', [MainController::class, 'index'])
    ->where('any', '.*')
    ->name('spa');
