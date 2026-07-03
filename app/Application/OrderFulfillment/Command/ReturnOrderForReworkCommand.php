<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class ReturnOrderForReworkCommand
{
    public function __construct(
        public int $orderId,
        public int $managerId,
        public string $feedback,
    ) {}
}
