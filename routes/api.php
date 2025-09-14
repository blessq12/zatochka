<?php

use Illuminate\Support\Facades\Route;


Route::controller(\App\Http\Controllers\Api\AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
});


Route::middleware('auth:sanctum')->group(function () {

    //
});
