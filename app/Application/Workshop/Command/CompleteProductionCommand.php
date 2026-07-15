<?php

namespace App\Application\Workshop\Command;

final readonly class CompleteProductionCommand
{
    public function __construct(
        public int $productionTaskId,
    ) {}
}
