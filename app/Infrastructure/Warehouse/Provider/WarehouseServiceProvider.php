<?php

namespace App\Infrastructure\Warehouse\Provider;

use App\Domain\Warehouse\Repository\OrderIssueRepository;
use App\Domain\Warehouse\Repository\StockItemRepository;
use App\Infrastructure\Warehouse\Repository\EloquentOrderIssueRepository;
use App\Infrastructure\Warehouse\Repository\EloquentStockItemRepository;
use App\Providers\ContextServiceProvider;

final class WarehouseServiceProvider extends ContextServiceProvider
{
    protected function bindings(): array
    {
        return [
            StockItemRepository::class => EloquentStockItemRepository::class,
            OrderIssueRepository::class => EloquentOrderIssueRepository::class,
        ];
    }
}
