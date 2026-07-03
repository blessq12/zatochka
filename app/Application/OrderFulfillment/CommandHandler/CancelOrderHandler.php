<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\CancelOrderCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Event\OrderCancelled;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class CancelOrderHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(CancelOrderCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);
        $updated = $order->cancel();
        $saved = $this->orders->save($updated);

        event(new OrderCancelled($saved));

        return $saved;
    }
}
