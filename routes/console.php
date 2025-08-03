<?php

use Illuminate\Support\Facades\Schedule;
use App\Contracts\Reviews\IReviewFactory;


Schedule::call(function () {
    $reviewFactory = app(IReviewFactory::class);
    $services = $reviewFactory->callAllServices();

    foreach ($services as $service) {
        $service->getReviews();
    }
})->everyFiveSeconds();
