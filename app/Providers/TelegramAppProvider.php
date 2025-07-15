<?php

namespace App\Providers;

use App\Contracts\TelegramServiceContract;
use App\Contracts\TelegramMessageServiceContract;
use App\Contracts\TelegramWebhookServiceContract;
use App\Services\TelegramService;
use App\Services\TelegramMessageService;
use App\Services\TelegramWebhookService;
use Illuminate\Support\ServiceProvider;

class TelegramAppProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TelegramServiceContract::class, TelegramService::class);
        $this->app->bind(TelegramMessageServiceContract::class, TelegramMessageService::class);
        $this->app->bind(TelegramWebhookServiceContract::class, TelegramWebhookService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
