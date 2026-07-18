<?php

namespace App\Application\Pricing\Command;

use App\Domain\Pricing\Repository\WorkPriceRepository;

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
