<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Inventory\ValueObjects\Quantity;

class StockItemQuantityChanged extends DomainEvent
{
    public function __construct(
        private readonly int $stockItemId,
        private readonly Quantity $oldQuantity,
        private readonly Quantity $newQuantity,
        private readonly string $changeType
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'StockItemQuantityChanged';
    }

    public function eventData(): array
    {
        return [
            'stock_item_id' => $this->stockItemId,
            'old_quantity' => $this->oldQuantity->value(),
            'new_quantity' => $this->newQuantity->value(),
            'change_type' => $this->changeType,
            'difference' => $this->newQuantity->value() - $this->oldQuantity->value(),
        ];
    }

    public function stockItemId(): int
    {
        return $this->stockItemId;
    }

    public function oldQuantity(): Quantity
    {
        return $this->oldQuantity;
    }

    public function newQuantity(): Quantity
    {
        return $this->newQuantity;
    }

    public function changeType(): string
    {
        return $this->changeType;
    }
}
