<?php

namespace App\Domain\Order\Event;

use App\Domain\Order\VO\OrderId;
use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class OrderMasterAssigned implements DomainEvent
{
    public function __construct(
        public OrderId $orderId,
        public EntityId $masterId,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
