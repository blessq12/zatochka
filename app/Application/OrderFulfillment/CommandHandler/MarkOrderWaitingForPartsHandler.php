<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\MarkOrderWaitingForPartsCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class MarkOrderWaitingForPartsHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(MarkOrderWaitingForPartsCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);
        $this->assertMaster($order, $command->masterId);

        return $this->orders->save($order->markWaitingForParts());
    }

    private function assertMaster(Order $order, int $masterId): void
    {
        if ($order->masterId() !== $masterId) {
            throw new OrderPolicyViolation('Заказ назначен другому мастеру.');
        }
    }
}
