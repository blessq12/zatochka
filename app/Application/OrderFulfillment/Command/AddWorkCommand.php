<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class AddWorkCommand
{
    public function __construct(
        public int $orderId,
        public int $masterId,
        public string $description,
    ) {}
}
