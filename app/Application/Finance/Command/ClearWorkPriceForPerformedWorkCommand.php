<?php

namespace App\Application\Finance\Command;

final readonly class ClearWorkPriceForPerformedWorkCommand
{
    public function __construct(
        public int $performedWorkId,
    ) {}
}
