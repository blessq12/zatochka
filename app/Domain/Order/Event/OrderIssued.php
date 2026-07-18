<?php

namespace App\Domain\Order\Event;

use App\Domain\Order\VO\OrderId;
use App\Shared\Domain\DomainEvent;
use DateTimeImmutable;

final readonly class OrderIssued implements DomainEvent
{
    public function __construct(
        public OrderId $orderId,
        /** Метод оплаты (cash|card|transfer). Null для гарантийных заказов — Finance не создаёт Payment. */
        public ?string $paymentMethod = null,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable,
    ) {}

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
