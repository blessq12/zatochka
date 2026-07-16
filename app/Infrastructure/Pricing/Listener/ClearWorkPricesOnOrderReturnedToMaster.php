<?php

namespace App\Infrastructure\Pricing\Listener;

use App\Application\Pricing\Command\ClearWorkPricesForOrderCommand;
use App\Application\Pricing\Command\ClearWorkPricesForOrderHandler;
use App\Domain\Order\Event\OrderReturnedToMaster;

final readonly class ClearWorkPricesOnOrderReturnedToMaster
{
    public function __construct(
        private ClearWorkPricesForOrderHandler $clearWorkPrices,
    ) {}

    public function handle(OrderReturnedToMaster $event): void
    {
        $this->clearWorkPrices->handle(new ClearWorkPricesForOrderCommand(
            $event->orderId->value,
        ));
    }
}
