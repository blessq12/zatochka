<?php

namespace App\Infrastructure\Pricing\Listener;

use App\Application\Pricing\Command\ClearWorkPricesForOrderCommand;
use App\Application\Pricing\Command\ClearWorkPricesForOrderHandler;
use App\Domain\Order\Event\OrderCancelled;

final readonly class ClearWorkPricesOnOrderCancelled
{
    public function __construct(
        private ClearWorkPricesForOrderHandler $clearWorkPrices,
    ) {}

    public function handle(OrderCancelled $event): void
    {
        $this->clearWorkPrices->handle(new ClearWorkPricesForOrderCommand(
            $event->orderId->value,
        ));
    }
}
