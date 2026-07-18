<?php

namespace App\Application\Order\Command;

final readonly class IssueOrderCommand
{
    public function __construct(
        public string $orderId,
        public ?string $paymentMethod = null,
    ) {}
}
