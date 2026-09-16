<?php

use App\Http\Controllers\SiteContent\BootstrapController;
use App\Http\Controllers\SiteContent\LegalDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('bootstrap', BootstrapController::class);
Route::get('documents/{type}', LegalDocumentController::class);
