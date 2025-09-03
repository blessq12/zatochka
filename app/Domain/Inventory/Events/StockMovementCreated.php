<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Inventory\ValueObjects\StockItemId;
use App\Domain\Inventory\ValueObjects\WarehouseId;
use App\Domain\Inventory\ValueObjects\MovementType;
use App\Domain\Inventory\ValueObjects\Quantity;
use App\Domain\Inventory\ValueObjects\Money;

class StockMovementCreated extends DomainEvent
{
    public function __construct(
        private readonly StockItemId $stockItemId,
        private readonly WarehouseId $warehouseId,
        private readonly MovementType $movementType,
        private readonly Quantity $quantity,
        private readonly ?Money $unitPrice,
        private readonly ?Money $totalAmount,
        private readonly ?string $description,
        private readonly ?string $referenceNumber
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return 'StockMovementCreated';
    }

    public function eventData(): array
    {
        return [
            'stock_item_id' => (string) $this->stockItemId,
            'warehouse_id' => (string) $this->warehouseId,
            'movement_type' => (string) $this->movementType,
            'quantity' => $this->quantity->value(),
            'unit_price' => $this->unitPrice ? (string) $this->unitPrice : null,
            'total_amount' => $this->totalAmount ? (string) $this->totalAmount : null,
            'description' => $this->description,
            'reference_number' => $this->referenceNumber,
        ];
    }

    public function stockItemId(): StockItemId
    {
        return $this->stockItemId;
    }

    public function warehouseId(): WarehouseId
    {
        return $this->warehouseId;
    }

    public function movementType(): MovementType
    {
        return $this->movementType;
    }

    public function quantity(): Quantity
    {
        return $this->quantity;
    }

    public function unitPrice(): ?Money
    {
        return $this->unitPrice;
    }

    public function totalAmount(): ?Money
    {
        return $this->totalAmount;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function referenceNumber(): ?string
    {
        return $this->referenceNumber;
    }
}
