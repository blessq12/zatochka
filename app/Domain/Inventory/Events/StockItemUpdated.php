<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;

class StockItemUpdated extends DomainEvent
{
    public function __construct(
        private readonly int $stockItemId,
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
            'stock_item_id' => $this->stockItemId,
            'update_type' => $this->updateType,
        ];
    }

    public function stockItemId(): int
    {
        return $this->stockItemId;
    }

    public function updateType(): string
    {
        return $this->updateType;
    }
}
