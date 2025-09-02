<?php

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use App\Domain\Shared\Interfaces\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Domain\Shared\Interfaces\RoleServiceInterface;
use App\Infrastructure\Services\RoleServiceSpatie;
use App\Domain\Shared\Interfaces\PasswordHasherInterface;
use App\Infrastructure\Services\LaravelPasswordHasher;

class UsersDomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(RoleServiceInterface::class, RoleServiceSpatie::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
    }
}
