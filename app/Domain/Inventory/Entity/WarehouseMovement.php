<?php

namespace App\Domain\Inventory\Entity;

use App\Domain\Inventory\VO\MovementType;
use App\Domain\Inventory\VO\Quantity;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
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
        public ?Money $unitPrice = null,
        public ?EntityId $reversesMovementId = null,
    ) {}

    public function lineAmount(): ?Money
    {
        if ($this->unitPrice === null) {
            return null;
        }

        $line = round((float) $this->unitPrice->amount * (float) $this->quantity->value, 2);

        return new Money(number_format($line, 2, '.', ''), $this->unitPrice->currency);
    }
}
