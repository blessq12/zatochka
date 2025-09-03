<?php

namespace App\Domain\Inventory\Events;

use App\Domain\Shared\Events\DomainEvent;
use App\Domain\Inventory\ValueObjects\MovementType;
use App\Domain\Inventory\ValueObjects\Quantity;
use App\Domain\Inventory\ValueObjects\Money;

class StockMovementCreated extends DomainEvent
{
    public function __construct(
        private readonly int $stockItemId,
        private readonly int $warehouseId,
        private readonly MovementType $movementType,
        private readonly Quantity $quantity,
        private readonly ?int $orderId,
        private readonly ?int $repairId,
        private readonly ?string $supplier,
        private readonly ?Money $unitPrice,
        private readonly ?Money $totalAmount,
        private readonly ?string $description,
        private readonly ?string $referenceNumber,
        private readonly int $createdBy
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
            'stock_item_id' => $this->stockItemId,
            'warehouse_id' => $this->warehouseId,
            'movement_type' => (string) $this->movementType,
            'quantity' => $this->quantity->value(),
            'order_id' => $this->orderId,
            'repair_id' => $this->repairId,
            'supplier' => $this->supplier,
            'unit_price' => $this->unitPrice ? (string) $this->unitPrice : null,
            'total_amount' => $this->totalAmount ? (string) $this->totalAmount : null,
            'description' => $this->description,
            'reference_number' => $this->referenceNumber,
            'created_by' => $this->createdBy,
        ];
    }

    public function stockItemId(): int
    {
        return $this->stockItemId;
    }

    public function warehouseId(): int
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

    public function orderId(): ?int
    {
        return $this->orderId;
    }

    public function repairId(): ?int
    {
        return $this->repairId;
    }

    public function supplier(): ?string
    {
        return $this->supplier;
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

    public function createdBy(): int
    {
        return $this->createdBy;
    }
}
