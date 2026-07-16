<?php

namespace App\Application\Workshop\Command;

final readonly class PauseForPartsCommand
{
    public function __construct(
        public int $productionTaskId,
    ) {}
}
