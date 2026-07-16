<?php

namespace App\Domain\Finance\Event;

use App\Shared\Domain\DomainEvent;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final readonly class PaymentAccepted implements DomainEvent
{
    public function __construct(
        public EntityId $paymentId,
        public OrderId $orderId,
        public Money $amount,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
