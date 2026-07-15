<?php

namespace App\Domain\Order\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final readonly class OrderCreated implements DomainEvent
{
    public function __construct(
        public EntityId $orderId,
        public EntityId $clientId,
        public Money $estimatedCost,
        public DateTimeImmutable $createdAt,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
