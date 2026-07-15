<?php

namespace App\Domain\Pricing\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final readonly class PriceCalculated implements DomainEvent
{
    public function __construct(
        public EntityId $estimateId,
        public EntityId $orderItemId,
        public EntityId $itemPriceId,
        public Money $finalAmount,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
