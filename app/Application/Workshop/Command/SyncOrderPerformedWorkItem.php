<?php

namespace App\Application\Workshop\Command;

final readonly class SyncOrderPerformedWorkItem
{
    public function __construct(
        public string $text,
        public ?int $workId = null,
        public ?int $orderItemId = null,
        public ?int $equipmentComponentId = null,
    ) {}
}
