<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Shared\Events\EventBusInterface;
use App\Infrastructure\Events\EventBus;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EventBusInterface::class, EventBus::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
