<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Shared\ValueObject\EntityId;

final readonly class CloseOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private DomainEventPublisher $events,
    ) {}

    public function handle(CloseOrderCommand $command): void
    {
        $order = $this->orders->getById(new EntityId($command->orderId));
        $order->close();
        $this->orders->save($order);
        $this->events->publish($order->pullDomainEvents());
    }
}
