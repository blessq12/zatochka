<?php

use App\Http\Controllers\Order\PublishedReviewsController;
use Illuminate\Support\Facades\Route;

Route::get('reviews', PublishedReviewsController::class);
