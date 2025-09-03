<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;

class StockItemDeactivated extends DomainEvent
{
    public function __construct(
        private readonly int $stockItemId
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'StockItemDeactivated';
    }

    public function eventData(): array
    {
        return [
            'stock_item_id' => $this->stockItemId,
        ];
    }

    public function stockItemId(): int
    {
        return $this->stockItemId;
    }
}
