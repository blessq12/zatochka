<?php

namespace App\Domain\Delivery\Event;

use App\Shared\Domain\DomainEvent;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class DeliveryRequested implements DomainEvent
{
    public function __construct(
        public EntityId $deliveryRequestId,
        public OrderId $orderId,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
