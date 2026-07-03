<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\MarkOrderReadyCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Event\OrderReady;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;
use DateTimeImmutable;

final class MarkOrderReadyHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(MarkOrderReadyCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);
        $this->assertMaster($order, $command->masterId);

        $updated = $order->markReady(new DateTimeImmutable);
        $saved = $this->orders->save($updated);

        event(new OrderReady($saved));

        return $saved;
    }

    private function assertMaster(Order $order, int $masterId): void
    {
        if ($order->masterId() !== $masterId) {
            throw new OrderPolicyViolation('Заказ назначен другому мастеру.');
        }
    }
}
