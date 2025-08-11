<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('companies')) {
            View::share('company', Company::first());
        }

        // Регистрируем Observer для автоматических уведомлений
        Order::observe(OrderObserver::class);
    }
}
