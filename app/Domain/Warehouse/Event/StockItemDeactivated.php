<?php

namespace App\Domain\Warehouse\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class StockItemDeactivated extends ShouldBeStored
{
    public function __construct(
        public int $stockItemId,
        public int $deactivatedBy
    ) {}
}
