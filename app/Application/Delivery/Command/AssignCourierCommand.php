<?php

namespace App\Application\Delivery\Command;

final readonly class AssignCourierCommand
{
    public function __construct(
        public int $deliveryRequestId,
        public int $courierId,
    ) {}
}
