<?php

namespace App\Domain\Inventory\Entities;

use App\Domain\Inventory\ValueObjects\StockItemId;
use App\Domain\Inventory\ValueObjects\WarehouseId;
use App\Domain\Inventory\ValueObjects\MovementType;
use App\Domain\Inventory\ValueObjects\Quantity;
use App\Domain\Inventory\ValueObjects\Money;
use App\Domain\Shared\Interfaces\AggregateRoot;
use App\Domain\Inventory\Events\StockMovementCreated;
use App\Domain\Shared\ValueObjects\UuidValueObject;

class StockMovement implements AggregateRoot
{
    private UuidValueObject $id;
    private StockItemId $stockItemId;
    private WarehouseId $warehouseId;
    private MovementType $movementType;
    private Quantity $quantity;
    private ?UuidValueObject $orderId;
    private ?UuidValueObject $repairId;
    private ?string $supplier;
    private ?Money $unitPrice;
    private ?Money $totalAmount;
    private ?string $description;
    private ?string $referenceNumber;
    private \DateTimeImmutable $movementDate;
    private UuidValueObject $createdBy;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        UuidValueObject $id,
        StockItemId $stockItemId,
        WarehouseId $warehouseId,
        MovementType $movementType,
        Quantity $quantity,
        ?UuidValueObject $orderId = null,
        ?UuidValueObject $repairId = null,
        ?string $supplier = null,
        ?Money $unitPrice = null,
        ?Money $totalAmount = null,
        ?string $description = null,
        ?string $referenceNumber = null,
        ?UuidValueObject $createdBy = null
    ) {
        $this->id = $id;
        $this->stockItemId = $stockItemId;
        $this->warehouseId = $warehouseId;
        $this->movementType = $movementType;
        $this->quantity = $quantity;
        $this->orderId = $orderId;
        $this->repairId = $repairId;
        $this->supplier = $supplier;
        $this->unitPrice = $unitPrice;
        $this->totalAmount = $totalAmount;
        $this->description = $description;
        $this->referenceNumber = $referenceNumber;
        $this->movementDate = new \DateTimeImmutable();
        $this->createdBy = $createdBy ?? UuidValueObject::generate();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        UuidValueObject $id,
        StockItemId $stockItemId,
        WarehouseId $warehouseId,
        MovementType $movementType,
        Quantity $quantity,
        ?UuidValueObject $orderId = null,
        ?UuidValueObject $repairId = null,
        ?string $supplier = null,
        ?Money $unitPrice = null,
        ?Money $totalAmount = null,
        ?string $description = null,
        ?string $referenceNumber = null,
        ?UuidValueObject $createdBy = null
    ): self {
        $movement = new self(
            $id,
            $stockItemId,
            $warehouseId,
            $movementType,
            $quantity,
            $orderId,
            $repairId,
            $supplier,
            $unitPrice,
            $totalAmount,
            $description,
            $referenceNumber,
            $createdBy
        );

        $movement->recordEvent(new StockMovementCreated(
            $movement->stockItemId,
            $movement->warehouseId,
            $movement->movementType,
            $movement->quantity,
            $movement->unitPrice,
            $movement->totalAmount,
            $movement->description,
            $movement->referenceNumber
        ));

        return $movement;
    }

    public static function reconstitute(
        UuidValueObject $id,
        StockItemId $stockItemId,
        WarehouseId $warehouseId,
        MovementType $movementType,
        Quantity $quantity,
        ?UuidValueObject $orderId,
        ?UuidValueObject $repairId,
        ?string $supplier,
        ?Money $unitPrice,
        ?Money $totalAmount,
        ?string $description,
        ?string $referenceNumber,
        \DateTimeImmutable $movementDate,
        UuidValueObject $createdBy,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): self {
        $movement = new self(
            $id,
            $stockItemId,
            $warehouseId,
            $movementType,
            $quantity,
            $orderId,
            $repairId,
            $supplier,
            $unitPrice,
            $totalAmount,
            $description,
            $referenceNumber,
            $createdBy
        );
        $movement->movementDate = $movementDate;
        $movement->createdAt = $createdAt;
        $movement->updatedAt = $updatedAt;
        return $movement;
    }

    // Getters
    public function id(): UuidValueObject
    {
        return $this->id;
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
    public function orderId(): ?UuidValueObject
    {
        return $this->orderId;
    }
    public function repairId(): ?UuidValueObject
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
    public function movementDate(): \DateTimeImmutable
    {
        return $this->movementDate;
    }
    public function createdBy(): UuidValueObject
    {
        return $this->createdBy;
    }
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Business methods
    public function isIncoming(): bool
    {
        return $this->movementType->isIncoming();
    }

    public function isOutgoing(): bool
    {
        return $this->movementType->isOutgoing();
    }

    public function isAdjustment(): bool
    {
        return $this->movementType->isAdjustment();
    }

    public function isRelatedToOrder(): bool
    {
        return $this->orderId !== null;
    }

    public function isRelatedToRepair(): bool
    {
        return $this->repairId !== null;
    }

    public function hasPricing(): bool
    {
        return $this->unitPrice !== null || $this->totalAmount !== null;
    }

    public function calculateTotalAmount(): ?Money
    {
        if ($this->unitPrice && $this->quantity) {
            return $this->unitPrice->multiply($this->quantity->value());
        }
        return $this->totalAmount;
    }

    public function updateDescription(?string $newDescription): void
    {
        if ($this->description === $newDescription) {
            return;
        }
        $this->description = $newDescription;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateReferenceNumber(?string $newReferenceNumber): void
    {
        if ($this->referenceNumber === $newReferenceNumber) {
            return;
        }
        $this->referenceNumber = $newReferenceNumber;
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Event handling
    private array $events = [];
    protected function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }
    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
    public function hasEvents(): bool
    {
        return !empty($this->events);
    }
}
