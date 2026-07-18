<?php

use App\Http\Controllers\Feedback\PublishedReviewsController;
use Illuminate\Support\Facades\Route;

Route::get('reviews', PublishedReviewsController::class);
