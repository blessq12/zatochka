<?php

namespace App\Infrastructure\Order\Provider;

use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\Repository\OrderReviewRepository;
use App\Infrastructure\Order\Repository\EloquentOrderRepository;
use App\Infrastructure\Order\Repository\EloquentOrderReviewRepository;
use App\Providers\ContextServiceProvider;

final class OrderServiceProvider extends ContextServiceProvider
{
    protected function bindings(): array
    {
        return [
            OrderRepository::class => EloquentOrderRepository::class,
            OrderReviewRepository::class => EloquentOrderReviewRepository::class,
        ];
    }
}
