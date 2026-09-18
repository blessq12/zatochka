<?php

namespace App\Shared\IntegrationEvents;

final readonly class OrderAcceptedIntoWork
{
    public function __construct(
        public int $orderId,
        public int $masterId,
    ) {}
}
