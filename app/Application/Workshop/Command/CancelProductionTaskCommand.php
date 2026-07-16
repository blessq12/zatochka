<?php

namespace App\Application\Workshop\Command;

final readonly class CancelProductionTaskCommand
{
    public function __construct(
        public string $orderId,
    ) {}
}
