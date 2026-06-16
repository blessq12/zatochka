<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class ResumeOrderCommand
{
    public function __construct(
        public int $orderId,
        public int $masterId,
    ) {}
}
