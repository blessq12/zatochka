<?php

use App\Http\Controllers\Workshop\ProductionTaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('workshop/production-tasks')->group(function (): void {
    Route::get('queued', [ProductionTaskController::class, 'indexQueued']);
    Route::get('{productionTaskId}', [ProductionTaskController::class, 'show']);
    Route::post('{productionTaskId}/assign-master', [ProductionTaskController::class, 'assignMaster']);
    Route::post('{productionTaskId}/diagnosis', [ProductionTaskController::class, 'completeDiagnosis']);
    Route::post('{productionTaskId}/reject', [ProductionTaskController::class, 'reject']);
    Route::post('{productionTaskId}/start-work', [ProductionTaskController::class, 'startWork']);
    Route::post('{productionTaskId}/complete-work', [ProductionTaskController::class, 'completeWork']);
    Route::post('{productionTaskId}/complete', [ProductionTaskController::class, 'completeProduction']);
    Route::post('{productionTaskId}/comments', [ProductionTaskController::class, 'addComment']);
});
