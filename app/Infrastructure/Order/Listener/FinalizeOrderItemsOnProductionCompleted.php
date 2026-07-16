<?php

namespace App\Infrastructure\Order\Listener;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Workshop\Event\ProductionCompleted;

final readonly class FinalizeOrderItemsOnProductionCompleted
{
    public function __construct(
        private OrderRepository $orders,
        private DomainEventPublisher $events,
    ) {}

    public function handle(ProductionCompleted $event): void
    {
        $order = $this->orders->getById($event->orderId);
        $order->finalizeItemsAfterProduction();
        $this->orders->save($order);
        $this->events->publish($order->pullDomainEvents());
    }
}
