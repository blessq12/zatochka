<?php

namespace App\Domain\Warehouse\Event;

use App\Domain\Warehouse\Entity\WarehouseItem;

final readonly class StockReceived
{
    public function __construct(
        public WarehouseItem $item,
        public string $quantity,
    ) {}
}
