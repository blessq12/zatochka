<?php

use App\Http\Controllers\Workshop\ProductionTaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('workshop/production-tasks')->group(function (): void {
    Route::get('queued', [ProductionTaskController::class, 'indexQueued']);

    Route::middleware(['auth:sanctum', 'master'])->group(function (): void {
        Route::get('/', [ProductionTaskController::class, 'index']);
        Route::get('counts', [ProductionTaskController::class, 'counts']);
        Route::get('stats', [ProductionTaskController::class, 'stats']);
        Route::get('{productionTaskId}', [ProductionTaskController::class, 'show']);
        Route::post('{productionTaskId}/diagnosis', [ProductionTaskController::class, 'completeDiagnosis']);
        Route::post('{productionTaskId}/start-work', [ProductionTaskController::class, 'startWork']);
        Route::post('{productionTaskId}/waiting-parts', [ProductionTaskController::class, 'pauseForParts']);
        Route::post('{productionTaskId}/resume', [ProductionTaskController::class, 'resume']);
        Route::post('{productionTaskId}/complete-work', [ProductionTaskController::class, 'completeWork']);
        Route::post('{productionTaskId}/complete', [ProductionTaskController::class, 'completeProduction']);
        Route::post('{productionTaskId}/finish', [ProductionTaskController::class, 'finish']);
        Route::post('{productionTaskId}/comments', [ProductionTaskController::class, 'addComment']);
        Route::post('{productionTaskId}/works', [ProductionTaskController::class, 'addWork']);
        Route::delete('{productionTaskId}/comments/{commentId}', [ProductionTaskController::class, 'removeComment']);
        Route::delete('{productionTaskId}/works/{workId}', [ProductionTaskController::class, 'removeWork']);
    });

    Route::post('{productionTaskId}/assign-master', [ProductionTaskController::class, 'assignMaster']);
});
