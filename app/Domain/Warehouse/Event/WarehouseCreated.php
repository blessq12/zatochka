<?php

namespace App\Domain\Warehouse\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class WarehouseCreated extends ShouldBeStored
{
    public function __construct(
        public int $warehouseId,
        public int $branchId,
        public string $name,
        public ?string $description,
        public int $createdBy
    ) {}
}
