<?php

namespace App\Shared\IntegrationEvents;

final readonly class OrderReturnedToRework
{
    public function __construct(
        public int $orderId,
    ) {}
}
