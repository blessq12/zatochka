<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Здесь будем регистрировать только нужные сервисы
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
