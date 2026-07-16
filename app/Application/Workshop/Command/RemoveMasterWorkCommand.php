<?php

namespace App\Application\Workshop\Command;

final readonly class RemoveMasterWorkCommand
{
    public function __construct(
        public int $productionTaskId,
        public int $workId,
        public int $masterId,
    ) {}
}
