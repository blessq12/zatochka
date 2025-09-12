<?php

use Illuminate\Support\Facades\Route;


Route::controller(\App\Http\Controllers\Api\TestController::class)->group(function () {
    Route::post('/order/create', 'createOrder');
    Route::get('/order/get/{id}', 'getOrder');
    Route::post('/order/update/{id}', 'updateOrder');
    Route::delete('/order/delete/{id}', 'deleteOrder');
});
