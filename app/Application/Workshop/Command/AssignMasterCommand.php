<?php

namespace App\Application\Workshop\Command;

final readonly class AssignMasterCommand
{
    public function __construct(
        public int $productionTaskId,
        public int $masterId,
    ) {}
}
