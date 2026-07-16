<?php

namespace App\Application\Workshop\Command;

final readonly class AddMasterWorkCommand
{
    public function __construct(
        public int $productionTaskId,
        public int $workId,
        public int $masterId,
        public string $text,
        public int $orderItemId,
    ) {}
}
