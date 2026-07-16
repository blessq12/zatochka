<?php

namespace App\Application\Workshop\Command;

final readonly class ResumeFromPartsCommand
{
    public function __construct(
        public int $productionTaskId,
    ) {}
}
