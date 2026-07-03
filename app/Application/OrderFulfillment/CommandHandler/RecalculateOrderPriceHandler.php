<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\RecalculateOrderPriceCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Event\OrderPriceRecalculated;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class RecalculateOrderPriceHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(RecalculateOrderPriceCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);
        $updated = $order->recalculatePrice();
        $saved = $this->orders->save($updated);

        event(new OrderPriceRecalculated($saved));

        return $saved;
    }
}
