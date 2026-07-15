<?php

namespace App\Application\Workshop\Command;

final readonly class CompleteWorkCommand
{
    public function __construct(
        public int $productionTaskId,
    ) {}
}
