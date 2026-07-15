<?php

namespace App\Domain\Pricing\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final readonly class DiscountApplied implements DomainEvent
{
    public function __construct(
        public EntityId $estimateId,
        public EntityId $itemPriceId,
        public EntityId $discountId,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
