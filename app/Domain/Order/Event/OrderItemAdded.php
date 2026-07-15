<?php

namespace App\Domain\Order\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class OrderItemAdded implements DomainEvent
{
    public function __construct(
        public EntityId $orderId,
        public EntityId $orderItemId,
        public ?EntityId $clientEquipmentId = null,
        public ?string $toolName = null,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
