<?php

use Illuminate\Support\Facades\Route;


Route::controller(\App\Http\Controllers\Api\TestController::class)->group(function () {
    Route::post('/review/create', 'create');
});
