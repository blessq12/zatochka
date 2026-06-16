<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\IssueOrderCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Event\OrderIssued;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;
use DateTimeImmutable;

final class IssueOrderHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(IssueOrderCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);
        $updated = $order->issue(new DateTimeImmutable);
        $saved = $this->orders->save($updated);

        event(new OrderIssued($saved));

        return $saved;
    }
}
