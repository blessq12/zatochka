<?php

namespace App\Domain\Warehouse\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class WarehouseUpdated extends ShouldBeStored
{
    public function __construct(
        public int $warehouseId,
        public string $name,
        public ?string $description,
        public int $updatedBy
    ) {}
}
