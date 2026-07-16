<?php

namespace App\Application\Workshop\Command;

final readonly class OpenProductionTaskCommand
{
    public function __construct(
        public int $productionTaskId,
        public string $orderId,
    ) {}
}
