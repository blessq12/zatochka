<?php

namespace App\Domain\Warehouse\Entities;

use App\Domain\Warehouse\Enums\StockMovementType;

final class StockMovement
{
    public function __construct(
        private ?int $id,
        private int $warehouseItemId,
        private StockMovementType $type,
        private string $quantity,
        private ?string $comment,
        private ?int $userId,
        private ?int $orderId,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function warehouseItemId(): int
    {
        return $this->warehouseItemId;
    }

    public function type(): StockMovementType
    {
        return $this->type;
    }

    public function quantity(): string
    {
        return $this->quantity;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function userId(): ?int
    {
        return $this->userId;
    }

    public function orderId(): ?int
    {
        return $this->orderId;
    }
}
