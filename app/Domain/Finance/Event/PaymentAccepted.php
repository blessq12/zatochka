<?php

namespace App\Domain\Finance\Event;

use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final readonly class PaymentAccepted implements DomainEvent
{
    public function __construct(
        public EntityId $paymentId,
        public EntityId $orderId,
        public Money $amount,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
