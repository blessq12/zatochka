<?php

namespace App\Domain\Warehouse\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class StockItemCreated extends ShouldBeStored
{
    public function __construct(
        public int $stockItemId,
        public int $warehouseId,
        public int $categoryId,
        public string $name,
        public string $sku,
        public ?string $description,
        public ?float $purchasePrice,
        public ?float $retailPrice,
        public int $quantity,
        public int $minStock,
        public string $unit,
        public ?string $supplier,
        public ?string $manufacturer,
        public ?string $model,
        public int $createdBy
    ) {}
}
