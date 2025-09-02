<?php

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;
use App\Domain\Bonuses\Contracts\BonusAccountRepository as BonusAccountRepositoryContract;
use App\Domain\Bonuses\Contracts\BonusTransactionRepository as BonusTransactionRepositoryContract;
use App\Domain\Bonuses\Contracts\SettingsProvider as BonusSettingsProviderContract;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentBonusAccountRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentBonusTransactionRepository;
use App\Infrastructure\Services\BonusSettingsProvider;

class BonusesDomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BonusAccountRepositoryContract::class, EloquentBonusAccountRepository::class);
        $this->app->bind(BonusTransactionRepositoryContract::class, EloquentBonusTransactionRepository::class);
        $this->app->bind(BonusSettingsProviderContract::class, BonusSettingsProvider::class);
    }
}
