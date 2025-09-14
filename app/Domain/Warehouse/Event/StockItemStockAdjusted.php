<?php

namespace App\Domain\Warehouse\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class StockItemStockAdjusted extends ShouldBeStored
{
    public function __construct(
        public int $stockItemId,
        public int $previousQuantity,
        public int $newQuantity,
        public int $adjustmentQuantity,
        public string $reason,
        public ?int $userId
    ) {}
}
