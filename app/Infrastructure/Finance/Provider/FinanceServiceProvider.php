<?php

namespace App\Infrastructure\Finance\Provider;

use App\Application\Finance\Listener\RecordCashIncomeOnOrderIssued;
use App\Domain\Finance\Repository\CashEntryRepository;
use App\Domain\Finance\Repository\EarningsGoalRepository;
use App\Domain\Finance\Repository\OrderPricingRepository;
use App\Infrastructure\Finance\Repository\EloquentCashEntryRepository;
use App\Infrastructure\Finance\Repository\EloquentEarningsGoalRepository;
use App\Infrastructure\Finance\Repository\EloquentOrderPricingRepository;
use App\Providers\ContextServiceProvider;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\OrderIssued;

final class FinanceServiceProvider extends ContextServiceProvider
{
    protected function bindings(): array
    {
        return [
            OrderPricingRepository::class => EloquentOrderPricingRepository::class,
            CashEntryRepository::class => EloquentCashEntryRepository::class,
            EarningsGoalRepository::class => EloquentEarningsGoalRepository::class,
        ];
    }

    public function boot(): void
    {
        $this->app->make(EventBus::class)->listen(
            OrderIssued::class,
            RecordCashIncomeOnOrderIssued::class,
        );
    }
}
