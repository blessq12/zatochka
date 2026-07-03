<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\RemoveMaterialFromOrderCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class RemoveMaterialFromOrderHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(RemoveMaterialFromOrderCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);

        return $this->orders->save($order->removeMaterial($command->materialId));
    }
}
