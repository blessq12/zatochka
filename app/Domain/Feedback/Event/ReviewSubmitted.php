<?php

namespace App\Domain\Feedback\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class ReviewSubmitted implements DomainEvent
{
    public function __construct(
        public EntityId $reviewId,
        public EntityId $orderId,
        public EntityId $clientId,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
