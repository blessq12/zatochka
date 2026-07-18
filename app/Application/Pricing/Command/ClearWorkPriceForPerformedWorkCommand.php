<?php

namespace App\Application\Pricing\Command;

final readonly class ClearWorkPriceForPerformedWorkCommand
{
    public function __construct(
        public int $performedWorkId,
    ) {}
}
