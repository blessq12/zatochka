<?php

namespace App\Application\Workshop\Command;

final readonly class RejectElementCommand
{
    public function __construct(
        public int $productionTaskId,
        public string $reason,
    ) {}
}
