<?php

namespace App\Domain\Warehouse\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class StockCategoryDeactivated extends ShouldBeStored
{
    public function __construct(
        public int $categoryId,
        public int $deactivatedBy
    ) {}
}
