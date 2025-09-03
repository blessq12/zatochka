<?php

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use App\Domain\Company\Services\CompanyService;
use App\Domain\Company\Services\BranchService;
use App\Domain\Company\Interfaces\CompanyRepositoryInterface;
use App\Domain\Company\Interfaces\BranchRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCompanyRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentBranchRepository;
use App\Infrastructure\Events\EventBus;

class CompanyDomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Регистрируем репозитории
        $this->app->bind(CompanyRepositoryInterface::class, EloquentCompanyRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, EloquentBranchRepository::class);

        // Регистрируем доменные сервисы
        $this->app->bind(CompanyService::class, function ($app) {
            return new CompanyService(
                $app->make(CompanyRepositoryInterface::class),
                $app->make(BranchRepositoryInterface::class),
                $app->make(EventBus::class)
            );
        });

        $this->app->bind(BranchService::class, function ($app) {
            return new BranchService(
                $app->make(BranchRepositoryInterface::class),
                $app->make(CompanyRepositoryInterface::class),
                $app->make(EventBus::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
