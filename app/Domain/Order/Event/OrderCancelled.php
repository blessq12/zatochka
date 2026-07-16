<?php

namespace App\Domain\Order\Event;

use App\Shared\Domain\DomainEvent;
use App\Domain\Order\VO\OrderId;
use DateTimeImmutable;

final readonly class OrderCancelled implements DomainEvent
{
    public function __construct(
        public OrderId $orderId,
        public string $reason,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
