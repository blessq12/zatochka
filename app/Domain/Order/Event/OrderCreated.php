<?php

namespace App\Domain\Order\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class OrderCreated extends ShouldBeStored
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $clientId,
        public readonly string $orderNumber,
        public readonly array $orderData
    ) {}
}
