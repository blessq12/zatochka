<?php

namespace App\Domain\Warehouse\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class WarehouseDeactivated extends ShouldBeStored
{
    public function __construct(
        public int $warehouseId,
        public int $deactivatedBy
    ) {}
}
