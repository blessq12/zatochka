<?php

namespace App\Shared\IntegrationEvents;

final readonly class OrderIssued
{
    public function __construct(
        public int $orderId,
        public string $billingType,
    ) {}
}
