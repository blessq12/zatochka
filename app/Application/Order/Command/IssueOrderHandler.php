<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;

final readonly class IssueOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private DomainEventPublisher $events,
    ) {}

    public function handle(IssueOrderCommand $command): void
    {
        $order = $this->orders->getById(new OrderId($command->orderId));
        $order->issue();
        $this->orders->save($order);
        $this->events->publish($order->pullDomainEvents());
    }
}
