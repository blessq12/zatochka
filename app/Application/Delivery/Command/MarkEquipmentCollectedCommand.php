<?php

namespace App\Application\Delivery\Command;

final readonly class MarkEquipmentCollectedCommand
{
    public function __construct(
        public int $deliveryRequestId,
    ) {}
}
