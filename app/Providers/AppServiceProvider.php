<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Shared\Interfaces\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Domain\Shared\Interfaces\RoleServiceInterface;
use App\Infrastructure\Services\RoleServiceSpatie;
use App\Domain\Shared\Interfaces\PasswordHasherInterface;
use App\Infrastructure\Services\LaravelPasswordHasher;
use App\Domain\Shared\Events\EventBusInterface;
use App\Infrastructure\Events\EventBus;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(RoleServiceInterface::class, RoleServiceSpatie::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
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
