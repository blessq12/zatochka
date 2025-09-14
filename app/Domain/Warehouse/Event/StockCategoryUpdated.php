<?php

namespace App\Domain\Warehouse\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class StockCategoryUpdated extends ShouldBeStored
{
    public function __construct(
        public int $categoryId,
        public string $name,
        public ?string $description,
        public string $color,
        public int $sortOrder,
        public int $updatedBy
    ) {}
}
