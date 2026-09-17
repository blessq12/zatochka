<?php

use App\Http\Controllers\Crm\ActorController;
use App\Http\Controllers\Crm\EquipmentController;
use App\Http\Controllers\Identity\IdentityController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\SiteContent\SiteContentController;
use Illuminate\Support\Facades\Route;

Route::post('/identity/register', [IdentityController::class, 'register']);
Route::post('/identity/login', [IdentityController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/identity/logout', [IdentityController::class, 'logout']);
    Route::get('/identity/me', [IdentityController::class, 'me']);

    Route::middleware('actor:managers')->group(function (): void {
        Route::get('/actors/{type}', [ActorController::class, 'index']);
        Route::post('/actors/clients/walk-in', [ActorController::class, 'storeWalkInClient']);
        Route::post('/actors/{type}', [IdentityController::class, 'provisionActor']);
        Route::delete('/actors/{type}/{id}', [ActorController::class, 'destroy']);

        Route::get('/site-content', [SiteContentController::class, 'show']);
        Route::put('/site-content/{section}', [SiteContentController::class, 'updateSection'])
            ->whereIn('section', ['company', 'contacts', 'schedule', 'faq', 'delivery', 'prices']);
        Route::put('/site-content/legal', [SiteContentController::class, 'saveLegal']);
        Route::delete('/site-content/legal/{slug}', [SiteContentController::class, 'destroyLegal']);

        Route::get('/equipments', [EquipmentController::class, 'index']);
        Route::post('/equipments', [EquipmentController::class, 'store']);
        Route::get('/equipments/{id}', [EquipmentController::class, 'show']);
        Route::match(['put', 'patch'], '/equipments/{id}', [EquipmentController::class, 'update']);
        Route::delete('/equipments/{id}', [EquipmentController::class, 'destroy']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::put('/orders/{id}/items', [OrderController::class, 'updateItems']);
        Route::post('/orders/{id}/assign-master', [OrderController::class, 'assignMaster']);
        Route::post('/orders/{id}/transition', [OrderController::class, 'transition']);
    });

    Route::middleware('actor:clients,managers')->group(function (): void {
        Route::get('/orders/{id}', [OrderController::class, 'show']);
    });

    Route::middleware('actor:clients')->group(function (): void {
        Route::post('/orders/{id}/review', [OrderController::class, 'storeReview']);
    });

    Route::middleware('actor:clients,managers,masters')->group(function (): void {
        Route::get('/actors/{type}/{id}', [ActorController::class, 'show']);
        Route::match(['put', 'patch'], '/actors/{type}/{id}', [ActorController::class, 'update']);
    });
});
