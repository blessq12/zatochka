<?php

namespace App\Application\Workshop\Command;

final readonly class OpenProductionTaskCommand
{
    public function __construct(
        public string $orderId,
        public ?int $productionTaskId = null,
    ) {}
}
