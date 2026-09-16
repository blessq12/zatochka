<?php

use App\Http\Controllers\Feedback\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('reviews')->middleware(['auth:sanctum', 'manager'])->group(function (): void {
    Route::get('/', [ReviewController::class, 'index']);
    Route::get('{reviewId}', [ReviewController::class, 'show'])->whereNumber('reviewId');
    Route::post('{reviewId}/publish', [ReviewController::class, 'publish'])->whereNumber('reviewId');
    Route::post('{reviewId}/reject', [ReviewController::class, 'reject'])->whereNumber('reviewId');
    Route::post('{reviewId}/hide', [ReviewController::class, 'hide'])->whereNumber('reviewId');
    Route::post('{reviewId}/restore', [ReviewController::class, 'restore'])->whereNumber('reviewId');
    Route::post('{reviewId}/reply', [ReviewController::class, 'reply'])->whereNumber('reviewId');
    Route::delete('{reviewId}', [ReviewController::class, 'destroy'])->whereNumber('reviewId');
});
