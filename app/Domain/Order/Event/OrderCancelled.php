<?php

namespace App\Domain\Order\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class OrderCancelled implements DomainEvent
{
    public function __construct(
        public EntityId $orderId,
        public string $reason,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
