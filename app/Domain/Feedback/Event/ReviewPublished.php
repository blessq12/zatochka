<?php

namespace App\Domain\Feedback\Event;

use App\Shared\Domain\DomainEvent;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class ReviewPublished implements DomainEvent
{
    public function __construct(
        public EntityId $reviewId,
        public OrderId $orderId,
        public EntityId $moderatorId,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
