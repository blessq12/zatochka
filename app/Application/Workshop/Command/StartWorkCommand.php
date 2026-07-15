<?php

namespace App\Application\Workshop\Command;

final readonly class StartWorkCommand
{
    public function __construct(
        public int $productionTaskId,
        public int $workExecutionId,
        public string $description,
    ) {}
}
