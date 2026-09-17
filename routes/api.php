<?php

use App\Http\Controllers\Crm\ActorController;
use App\Http\Controllers\Identity\IdentityController;
use Illuminate\Support\Facades\Route;

Route::post('/identity/register', [IdentityController::class, 'register']);
Route::post('/identity/login', [IdentityController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/identity/logout', [IdentityController::class, 'logout']);
    Route::get('/identity/me', [IdentityController::class, 'me']);

    Route::middleware('actor:managers')->group(function (): void {
        Route::get('/actors/{type}', [ActorController::class, 'index']);
        Route::post('/actors/{type}', [IdentityController::class, 'provisionActor']);
        Route::delete('/actors/{type}/{id}', [ActorController::class, 'destroy']);
    });

    Route::middleware('actor:clients,managers,masters')->group(function (): void {
        Route::get('/actors/{type}/{id}', [ActorController::class, 'show']);
        Route::match(['put', 'patch'], '/actors/{type}/{id}', [ActorController::class, 'update']);
    });
});
