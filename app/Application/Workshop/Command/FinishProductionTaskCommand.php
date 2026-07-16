<?php

namespace App\Application\Workshop\Command;

final readonly class FinishProductionTaskCommand
{
    public function __construct(
        public int $productionTaskId,
    ) {}
}
