<?php

namespace App\Application\Delivery\Command;

final readonly class MarkOrderDeliveredCommand
{
    public function __construct(
        public int $deliveryRequestId,
    ) {}
}
