<?php

namespace App\Application\Pricing\Command;

use App\Domain\Pricing\Repository\WorkPriceRepository;

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
