<?php

namespace App\Application\Workshop\Command;

final readonly class EnsureProductionTaskOpenedAndAssignedCommand
{
    public function __construct(
        public string $orderId,
        public int $masterId,
    ) {}
}
