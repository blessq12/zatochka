<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class IssueOrderCommand
{
    public function __construct(
        public int $orderId,
    ) {}
}
