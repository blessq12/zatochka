<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\TakeOrderToWorkCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Event\OrderTakenToWork;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;
use DateTimeImmutable;

final class TakeOrderToWorkHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(TakeOrderToWorkCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);
        $updated = $order->takeToWork($command->masterId, new DateTimeImmutable);
        $saved = $this->orders->save($updated);

        event(new OrderTakenToWork($saved));

        return $saved;
    }
}
