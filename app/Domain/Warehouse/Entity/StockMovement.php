<?php

namespace App\Domain\Warehouse\Entity;

use App\Domain\Warehouse\Enum\StockMovementType;

readonly class StockMovement
{
    public function __construct(
        public ?int $id,
        public int $stockItemId,
        public string $movementType,
        public int $quantity,
        public ?int $previousQuantity,
        public ?int $newQuantity,
        public ?string $reason,
        public ?int $orderId,
        public ?int $userId,
        public ?float $unitPrice,
        public ?string $reference,
        public ?\DateTime $createdAt = null,
    ) {}

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStockItemId(): int
    {
        return $this->stockItemId;
    }

    public function getMovementType(): string
    {
        return $this->movementType;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPreviousQuantity(): ?int
    {
        return $this->previousQuantity;
    }

    public function getNewQuantity(): ?int
    {
        return $this->newQuantity;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getUnitPrice(): ?float
    {
        return $this->unitPrice;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    // Business methods
    public function isInbound(): bool
    {
        return StockMovementType::tryFrom($this->movementType)?->isInbound() ?? false;
    }

    public function isOutbound(): bool
    {
        return StockMovementType::tryFrom($this->movementType)?->isOutbound() ?? false;
    }

    public function isAdjustment(): bool
    {
        return str_starts_with($this->movementType, 'adjustment');
    }

    public function hasValidQuantities(): bool
    {
        if ($this->previousQuantity === null || $this->newQuantity === null) {
            return true; // Для старых записей
        }

        return $this->newQuantity === $this->previousQuantity + ($this->isInbound() ? $this->quantity : -$this->quantity);
    }

    public function getDisplayReason(): string
    {
        if ($this->reason) {
            return $this->reason;
        }

        return match ($this->movementType) {
            'receipt' => 'Поступление товара',
            'issue' => 'Выдача товара',
            'adjustment_in' => 'Корректировка (увеличение)',
            'adjustment_out' => 'Корректировка (уменьшение)',
            'return' => 'Возврат товара',
            'damage' => 'Списание (повреждение)',
            'expired' => 'Списание (истечение срока)',
            default => 'Движение товара',
        };
    }

    public function getTotalValue(): ?float
    {
        return $this->unitPrice ? $this->quantity * $this->unitPrice : null;
    }
}
