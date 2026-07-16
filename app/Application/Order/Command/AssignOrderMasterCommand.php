<?php

namespace App\Application\Order\Command;

final readonly class AssignOrderMasterCommand
{
    public function __construct(
        public string $orderId,
        public int $masterId,
    ) {}
}
