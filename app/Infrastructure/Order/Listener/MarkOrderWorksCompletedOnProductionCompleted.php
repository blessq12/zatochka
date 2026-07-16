<?php

namespace App\Infrastructure\Order\Listener;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Workshop\Event\ProductionCompleted;
use App\Shared\Domain\DomainException;

/**
 * When master finishes the production task: finalize items and move order to works_completed.
 */
final readonly class MarkOrderWorksCompletedOnProductionCompleted
{
    public function __construct(
        private OrderRepository $orders,
        private DomainEventPublisher $events,
    ) {}

    public function handle(ProductionCompleted $event): void
    {
        $order = $this->orders->getById($event->orderId);

        $order->finalizeItemsAfterProduction();

        if ($order->status() !== OrderStatus::InProgress
            && $order->status() !== OrderStatus::WorksCompleted
        ) {
            throw new DomainException(sprintf(
                'Order cannot enter works completed from status %s.',
                $order->status()->value,
            ));
        }

        $order->markWorksCompleted();
        $this->orders->save($order);
        $this->events->publish($order->pullDomainEvents());
    }
}
