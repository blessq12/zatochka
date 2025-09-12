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
        // mappers
        $this->app->singleton(\App\Domain\Order\Mapper\OrderMapper::class, \App\Infrastructure\Mapper\OrderMapperImpl::class);

        // repositories implementation
        $this->app->singleton(\App\Domain\Order\Repository\OrderRepository::class, \App\Infrastructure\Repository\Order\OrderRepositoryImpl::class);

        // domain services
        $this->app->singleton(\App\Domain\Order\Service\OrderNumberGeneratorService::class, function ($app) {
            return new \App\Domain\Order\Service\OrderNumberGeneratorService(
                $app->make(\App\Domain\Order\Repository\OrderRepository::class)
            );
        });

        $this->app->singleton(\App\Domain\Order\Service\OrderStatusGroupingService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
