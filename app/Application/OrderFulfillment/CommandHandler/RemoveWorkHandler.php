<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\RemoveWorkCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class RemoveWorkHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(RemoveWorkCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);
        $this->assertMaster($order, $command->masterId);

        return $this->orders->save($order->removeWork($command->sortOrder));
    }

    private function assertMaster(Order $order, int $masterId): void
    {
        if ($order->masterId() !== $masterId) {
            throw new OrderPolicyViolation('Заказ назначен другому мастеру.');
        }
    }
}
