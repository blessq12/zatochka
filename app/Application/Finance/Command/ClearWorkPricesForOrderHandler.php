<?php

namespace App\Application\Finance\Command;

use App\Domain\Finance\Repository\WorkPriceRepository;

final readonly class ClearWorkPricesForOrderHandler
{
    public function __construct(
        private WorkPriceRepository $workPrices,
    ) {}

    public function handle(ClearWorkPricesForOrderCommand $command): void
    {
        $this->workPrices->deleteByOrderId($command->orderId);
    }
}
