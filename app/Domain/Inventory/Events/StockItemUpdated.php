<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Inventory\ValueObjects\StockItemId;

class StockItemUpdated extends DomainEvent
{
    public function __construct(
        private readonly StockItemId $stockItemId,
        private readonly string $updateType
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'StockItemUpdated';
    }

    public function eventData(): array
    {
        return [
            'stock_item_id' => (string) $this->stockItemId,
            'update_type' => $this->updateType,
        ];
    }

    public function stockItemId(): StockItemId
    {
        return $this->stockItemId;
    }

    public function updateType(): string
    {
        return $this->updateType;
    }
}
