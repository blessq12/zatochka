<?php

namespace App\Providers;

use App\Contracts\Reviews\IReviewFactory;
use App\Contracts\Reviews\IReviewService;
use App\Services\Reviews\ReviewFactory;
use App\Services\Reviews\ReviewServices\TwoGisService;
use App\Services\Reviews\ReviewServices\YandexService;

use Illuminate\Support\ServiceProvider;

class ReviewProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(IReviewFactory::class, ReviewFactory::class);
        $this->app->bind(IReviewService::class, TwoGisService::class);
    }
}
