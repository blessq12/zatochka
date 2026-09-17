<?php

namespace App\Infrastructure\Finance\Provider;

use App\Domain\Finance\Repository\OrderPricingRepository;
use App\Infrastructure\Finance\Repository\EloquentOrderPricingRepository;
use App\Providers\ContextServiceProvider;

final class FinanceServiceProvider extends ContextServiceProvider
{
    protected function bindings(): array
    {
        return [
            OrderPricingRepository::class => EloquentOrderPricingRepository::class,
        ];
    }
}
