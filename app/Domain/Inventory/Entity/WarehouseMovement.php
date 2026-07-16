<?php

namespace App\Domain\Inventory\Entity;

use App\Domain\Inventory\VO\MovementType;
use App\Domain\Inventory\VO\Quantity;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class WarehouseMovement
{
    public function __construct(
        public EntityId $id,
        public MovementType $type,
        public Quantity $quantity,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable(),
        public ?string $comment = null,
        public ?string $orderId = null,
        public ?int $orderItemId = null,
    ) {}
}
