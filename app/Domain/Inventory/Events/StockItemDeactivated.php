<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Inventory\ValueObjects\StockItemId;

class StockItemDeactivated extends DomainEvent
{
    public function __construct(
        private readonly StockItemId $stockItemId
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
            'stock_item_id' => (string) $this->stockItemId,
        ];
    }

    public function stockItemId(): StockItemId
    {
        return $this->stockItemId;
    }
}
