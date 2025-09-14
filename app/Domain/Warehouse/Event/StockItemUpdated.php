<?php

namespace App\Domain\Warehouse\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class StockItemUpdated extends ShouldBeStored
{
    public function __construct(
        public int $stockItemId,
        public string $name,
        public string $sku,
        public ?string $description,
        public ?float $purchasePrice,
        public ?float $retailPrice,
        public int $minStock,
        public string $unit,
        public ?string $supplier,
        public ?string $manufacturer,
        public ?string $model,
        public int $updatedBy
    ) {}
}
