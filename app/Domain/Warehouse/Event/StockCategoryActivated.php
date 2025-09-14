<?php

namespace App\Domain\Warehouse\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class StockCategoryActivated extends ShouldBeStored
{
    public function __construct(
        public int $categoryId,
        public int $activatedBy
    ) {}
}
