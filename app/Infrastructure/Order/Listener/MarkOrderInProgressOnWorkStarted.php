<?php

namespace App\Infrastructure\Order\Listener;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Workshop\Event\WorkStarted;

final readonly class MarkOrderInProgressOnWorkStarted
{
    public function __construct(
        private OrderRepository $orders,
        private DomainEventPublisher $events,
    ) {}

    public function handle(WorkStarted $event): void
    {
        $order = $this->orders->getById($event->orderId);
        $order->markInProgress();
        $order->markItemsInProduction();
        $this->orders->save($order);
        $this->events->publish($order->pullDomainEvents());
    }
}
