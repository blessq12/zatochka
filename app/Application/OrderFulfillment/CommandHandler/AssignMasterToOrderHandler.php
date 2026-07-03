<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\AssignMasterToOrderCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class AssignMasterToOrderHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(AssignMasterToOrderCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);
        $updated = $order->assignMaster($command->masterId);

        return $this->orders->save($updated);
    }
}
