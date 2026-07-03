<?php

namespace App\Domain\Warehouse\Event;

use App\Domain\Warehouse\Entity\WarehouseItem;

final readonly class StockWrittenOff
{
    public function __construct(
        public WarehouseItem $item,
        public string $quantity,
    ) {}
}
