<?php

namespace App\Domain\Finance\Event;

use App\Domain\Finance\VO\PaymentMethod;
use App\Domain\Order\VO\OrderId;
use App\Shared\Domain\DomainEvent;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final readonly class PaymentAccepted implements DomainEvent
{
    public function __construct(
        public EntityId $paymentId,
        public OrderId $orderId,
        public string $orderNumber,
        public Money $amount,
        public PaymentMethod $method,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable,
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
