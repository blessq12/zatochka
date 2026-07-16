<?php

namespace App\Infrastructure\Order\Listener;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Workshop\Event\ProductionCompleted;
use App\Domain\Workshop\Repository\ProductionTaskRepository;
use App\Domain\Workshop\VO\ProductionStatus;

final readonly class MarkOrderAwaitingPricingWhenAllProductionCompleted
{
    public function __construct(
        private OrderRepository $orders,
        private ProductionTaskRepository $tasks,
        private DomainEventPublisher $events,
    ) {}

    public function handle(ProductionCompleted $event): void
    {
        $task = $this->tasks->findByOrderId($event->orderId);

        if ($task === null || $task->status() !== ProductionStatus::Completed) {
            return;
        }

        $order = $this->orders->getById($event->orderId);

        if (! $order->allItemsFinalized()) {
            return;
        }

        $order->markAwaitingPricing();
        $this->orders->save($order);
        $this->events->publish($order->pullDomainEvents());
    }
}
