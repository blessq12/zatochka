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
        $this->app->singleton(\App\Domain\Client\Mapper\ClientMapper::class, \App\Infrastructure\Client\Mapper\ClientMapperImpl::class);
        $this->app->singleton(\App\Domain\Review\Mapper\ReviewMapper::class, \App\Infrastructure\Review\Mapper\ReviewMapperImpl::class);
        $this->app->singleton(\App\Domain\Warehouse\Mapper\WarehouseMapper::class, \App\Infrastructure\Warehouse\Mapper\WarehouseMapperImpl::class);
        $this->app->singleton(\App\Domain\Company\Mapper\CompanyMapper::class, \App\Infrastructure\Company\Mapper\CompanyMapperImpl::class);
        $this->app->singleton(\App\Domain\Company\Mapper\UserMapper::class, \App\Infrastructure\Company\Mapper\UserMapperImpl::class);
        $this->app->singleton(\App\Domain\Warehouse\Mapper\StockCategoryMapper::class, \App\Infrastructure\Warehouse\Mapper\StockCategoryMapperImpl::class);
        $this->app->singleton(\App\Domain\Warehouse\Mapper\StockItemMapper::class, \App\Infrastructure\Warehouse\Mapper\StockItemMapperImpl::class);
        $this->app->singleton(\App\Domain\Warehouse\Mapper\StockMovementMapper::class, \App\Infrastructure\Warehouse\Mapper\StockMovementMapperImpl::class);

        // repositories implementation
        $this->app->singleton(\App\Domain\Order\Repository\OrderRepository::class, \App\Infrastructure\Repository\Order\OrderRepositoryImpl::class);
        $this->app->singleton(\App\Domain\Bonus\Repository\BonusAccountRepository::class, \App\Infrastructure\Bonus\Repository\BonusAccountRepositoryImpl::class);
        $this->app->singleton(\App\Domain\Bonus\Repository\BonusTransactionRepository::class, \App\Infrastructure\Bonus\Repository\BonusTransactionRepositoryImpl::class);
        $this->app->singleton(\App\Domain\Client\Repository\ClientRepository::class, \App\Infrastructure\Client\Repository\ClientRepositoryImpl::class);
        $this->app->singleton(\App\Domain\Review\Repository\ReviewRepository::class, \App\Infrastructure\Review\Repository\ReviewRepositoryImpl::class);
        $this->app->singleton(\App\Domain\Warehouse\Repository\WarehouseRepository::class, \App\Infrastructure\Warehouse\Repository\WarehouseRepositoryImpl::class);
        $this->app->singleton(\App\Domain\Company\Repository\CompanyRepository::class, \App\Infrastructure\Company\Repository\CompanyRepositoryImpl::class);
        $this->app->singleton(\App\Domain\Company\Repository\UserRepository::class, \App\Infrastructure\Company\Repository\UserRepositoryImpl::class);
        $this->app->singleton(\App\Domain\Warehouse\Repository\StockCategoryRepository::class, \App\Infrastructure\Warehouse\Repository\StockCategoryRepositoryImpl::class);
        $this->app->singleton(\App\Domain\Warehouse\Repository\StockItemRepository::class, \App\Infrastructure\Warehouse\Repository\StockItemRepositoryImpl::class);
        $this->app->singleton(\App\Domain\Warehouse\Repository\StockMovementRepository::class, \App\Infrastructure\Warehouse\Repository\StockMovementRepositoryImpl::class);

        // domain services
        $this->app->singleton(\App\Domain\Order\Service\OrderNumberGeneratorService::class, function ($app) {
            return new \App\Domain\Order\Service\OrderNumberGeneratorService(
                $app->make(\App\Domain\Order\Repository\OrderRepository::class)
            );
        });

        $this->app->singleton(\App\Domain\Order\Service\OrderStatusGroupingService::class);

        // auth context
        $this->app->singleton(\App\Domain\Auth\AuthContextInterface::class, \App\Infrastructure\Auth\AuthContextImpl::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
