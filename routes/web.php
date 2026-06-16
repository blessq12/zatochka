<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::get('/{any?}', [MainController::class, 'index'])
    ->where('any', '.*')
    ->name('spa');
