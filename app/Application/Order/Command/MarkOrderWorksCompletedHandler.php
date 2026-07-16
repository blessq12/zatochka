<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Shared\Domain\DomainException;

final readonly class MarkOrderWorksCompletedHandler
{
    public function __construct(
        private OrderRepository $orders,
        private DomainEventPublisher $events,
    ) {}

    public function handle(MarkOrderWorksCompletedCommand $command): void
    {
        $order = $this->orders->getById(new OrderId($command->orderId));
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
