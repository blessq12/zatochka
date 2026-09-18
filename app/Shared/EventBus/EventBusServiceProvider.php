<?php

namespace App\Shared\EventBus;

use App\Infrastructure\Shared\EventBus\LaravelEventBus;
use Illuminate\Support\ServiceProvider;

final class EventBusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EventBus::class, LaravelEventBus::class);
    }
}
