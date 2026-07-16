<?php

namespace App\Domain\Feedback\Event;

use App\Shared\Domain\DomainEvent;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class ReviewDeleted implements DomainEvent
{
    public function __construct(
        public EntityId $reviewId,
        public OrderId $orderId,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
