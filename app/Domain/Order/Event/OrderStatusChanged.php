<?php

namespace App\Domain\Order\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class OrderStatusChanged extends ShouldBeStored
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $oldStatus,
        public readonly string $newStatus,
        public readonly ?string $changedBy = null
    ) {}
}
