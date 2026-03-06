<?php

namespace App\Providers;

use App\Contracts\MessengerServiceInterface;
use App\Services\Messenger\MaxMessengerService;
use App\Services\Messenger\TelegramMessengerService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('messenger.telegram', TelegramMessengerService::class);
        $this->app->bind('messenger.max', MaxMessengerService::class);

        $this->app->bind(MessengerServiceInterface::class, function ($app) {
            $default = config('services.messenger.default', 'telegram');

            return $app->make($default === 'max' ? 'messenger.max' : 'messenger.telegram');
        });

        $this->app->when(\App\Http\Controllers\Api\PosController::class)
            ->needs(MessengerServiceInterface::class)
            ->give(TelegramMessengerService::class);

        $this->app->when(\App\Http\Controllers\Api\OrderController::class)
            ->needs(MessengerServiceInterface::class)
            ->give(TelegramMessengerService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
