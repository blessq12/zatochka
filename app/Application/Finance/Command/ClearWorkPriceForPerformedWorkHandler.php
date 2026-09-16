<?php

namespace App\Application\Finance\Command;

use App\Domain\Finance\Repository\WorkPriceRepository;

final readonly class ClearWorkPriceForPerformedWorkHandler
{
    public function __construct(
        private WorkPriceRepository $workPrices,
    ) {}

    public function handle(ClearWorkPriceForPerformedWorkCommand $command): void
    {
        $this->workPrices->deleteByPerformedWorkIds([$command->performedWorkId]);
    }
}
