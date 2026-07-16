<?php

namespace App\Domain\Order\Event;

use App\Domain\Order\VO\OrderId;
use App\Shared\Domain\DomainEvent;
use DateTimeImmutable;

final readonly class OrderReturnedToMaster implements DomainEvent
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
