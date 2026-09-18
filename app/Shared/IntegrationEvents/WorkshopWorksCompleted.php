<?php

namespace App\Shared\IntegrationEvents;

final readonly class WorkshopWorksCompleted
{
    public function __construct(
        public int $orderId,
    ) {}
}
