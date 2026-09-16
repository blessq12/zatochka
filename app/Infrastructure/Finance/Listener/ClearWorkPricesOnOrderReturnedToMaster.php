<?php

namespace App\Infrastructure\Finance\Listener;

use App\Application\Finance\Command\ClearWorkPricesForOrderCommand;
use App\Application\Finance\Command\ClearWorkPricesForOrderHandler;
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
