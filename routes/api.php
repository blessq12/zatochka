<?php

use App\Http\Controllers\ApplyOrderMaterialsController;
use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Crm\ActorController;
use App\Http\Controllers\Crm\EquipmentController;
use App\Http\Controllers\Finance\CashEntryController;
use App\Http\Controllers\Finance\EarningsGoalController;
use App\Http\Controllers\Finance\OrderPricingController;
use App\Http\Controllers\Identity\IdentityController;
use App\Http\Controllers\Order\DocumentTemplateController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\OrderDocumentController;
use App\Http\Controllers\Order\OrderDraftController;
use App\Http\Controllers\Order\PublicOrderDraftController;
use App\Http\Controllers\ProvisionActorController;
use App\Http\Controllers\SiteContent\SiteContentController;
use App\Http\Controllers\Warehouse\OrderIssueController;
use App\Http\Controllers\Warehouse\StockItemController;
use App\Http\Controllers\Workshop\WorkshopJobController;
use Illuminate\Support\Facades\Route;

Route::post('/identity/register', [IdentityController::class, 'register']);
Route::post('/identity/login', [IdentityController::class, 'login']);

Route::post('/public/order-drafts', [PublicOrderDraftController::class, 'store']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/identity/logout', [IdentityController::class, 'logout']);
    Route::get('/identity/me', [IdentityController::class, 'me']);

    Route::middleware('actor:managers')->group(function (): void {
        Route::get('/manager/dashboard', DashboardController::class);

        Route::get('/actors/{type}', [ActorController::class, 'index']);
        Route::post('/actors/clients/walk-in', [ActorController::class, 'storeWalkInClient']);
        Route::post('/actors/{type}', [ProvisionActorController::class, '__invoke']);
        Route::delete('/actors/{type}/{id}', [ActorController::class, 'destroy']);

        Route::get('/site-content', [SiteContentController::class, 'show']);
        Route::put('/site-content/{section}', [SiteContentController::class, 'updateSection'])
            ->whereIn('section', ['company', 'contacts', 'schedule', 'faq', 'delivery', 'prices']);
        Route::put('/site-content/legal', [SiteContentController::class, 'saveLegal']);
        Route::delete('/site-content/legal/{slug}', [SiteContentController::class, 'destroyLegal']);

        Route::post('/equipments', [EquipmentController::class, 'store']);
        Route::match(['put', 'patch'], '/equipments/{id}', [EquipmentController::class, 'update']);
        Route::delete('/equipments/{id}', [EquipmentController::class, 'destroy']);

        Route::put('/orders/{id}/items', [OrderController::class, 'updateItems']);
        Route::post('/orders/{id}/assign-master', [OrderController::class, 'assignMaster']);
        Route::post('/orders/{id}/transition', [OrderController::class, 'transition']);
        Route::get('/orders/{id}/documents/{type}', OrderDocumentController::class)
            ->whereIn('type', ['receipt', 'handover_act']);

        Route::get('/order-document-templates', [DocumentTemplateController::class, 'index']);
        Route::put('/order-document-templates/{type}', [DocumentTemplateController::class, 'update'])
            ->whereIn('type', ['receipt', 'handover_act']);
        Route::post('/order-document-templates/{type}/preview', [DocumentTemplateController::class, 'preview'])
            ->whereIn('type', ['receipt', 'handover_act']);

        Route::get('/finance/pricings/by-order/{orderId}', [OrderPricingController::class, 'byOrder']);
        Route::put('/finance/pricings/by-order/{orderId}', [OrderPricingController::class, 'upsertByOrder']);

        Route::get('/finance/cash-entries', [CashEntryController::class, 'index']);
        Route::post('/finance/cash-entries', [CashEntryController::class, 'store']);
        Route::delete('/finance/cash-entries/{id}', [CashEntryController::class, 'destroy']);

        Route::get('/finance/goals', [EarningsGoalController::class, 'index']);
        Route::post('/finance/goals', [EarningsGoalController::class, 'store']);
        Route::post('/finance/goals/{id}/cancel', [EarningsGoalController::class, 'cancel']);

        Route::post('/warehouse/items', [StockItemController::class, 'store']);
        Route::match(['put', 'patch'], '/warehouse/items/{id}', [StockItemController::class, 'update']);
        Route::post('/warehouse/items/{id}/receive', [StockItemController::class, 'receive']);
        Route::get('/warehouse/issues/by-order/{orderId}', [OrderIssueController::class, 'byOrder']);
        Route::put('/orders/{orderId}/materials', ApplyOrderMaterialsController::class);
    });

    Route::middleware('actor:masters')->group(function (): void {
        Route::get('/orders/assigned', [OrderController::class, 'assigned']);
    });

    Route::middleware('actor:managers,masters,clients')->group(function (): void {
        Route::get('/equipments', [EquipmentController::class, 'index']);
        Route::get('/equipments/{id}', [EquipmentController::class, 'show']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show'])->whereNumber('id');
    });

    Route::middleware('actor:managers,masters')->group(function (): void {
        Route::get('/warehouse/items', [StockItemController::class, 'index']);
        Route::get('/warehouse/items/{id}', [StockItemController::class, 'show']);
        Route::get('/workshop/jobs/by-order/{orderId}', [WorkshopJobController::class, 'byOrder']);
    });

    Route::middleware('actor:masters')->group(function (): void {
        Route::post('/workshop/jobs/accept', [WorkshopJobController::class, 'accept']);
        Route::get('/workshop/jobs/mine', [WorkshopJobController::class, 'mine']);
        Route::get('/workshop/jobs/{id}', [WorkshopJobController::class, 'show']);
        Route::put('/workshop/jobs/{id}/items/{orderItemId}', [WorkshopJobController::class, 'updateItem']);
        Route::post('/workshop/jobs/{id}/complete', [WorkshopJobController::class, 'complete']);
    });

    Route::middleware('actor:managers')->group(function (): void {
        Route::post('/orders', [OrderController::class, 'store']);
        Route::post('/order-drafts/{id}/promote', [OrderDraftController::class, 'promote'])->whereNumber('id');
    });

    Route::middleware('actor:managers,clients')->group(function (): void {
        Route::get('/order-drafts', [OrderDraftController::class, 'index']);
        Route::get('/order-drafts/{id}', [OrderDraftController::class, 'show'])->whereNumber('id');
        Route::put('/order-drafts/{id}', [OrderDraftController::class, 'update'])->whereNumber('id');
        Route::post('/order-drafts/{id}/cancel', [OrderDraftController::class, 'cancel'])->whereNumber('id');
    });

    Route::middleware('actor:clients')->group(function (): void {
        Route::post('/order-drafts', [OrderDraftController::class, 'store']);
        Route::post('/orders/{id}/review', [OrderController::class, 'storeReview']);
    });

    Route::middleware('actor:clients,managers,masters')->group(function (): void {
        Route::get('/actors/{type}/{id}', [ActorController::class, 'show']);
        Route::match(['put', 'patch'], '/actors/{type}/{id}', [ActorController::class, 'update']);
    });
});
