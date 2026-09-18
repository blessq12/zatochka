<?php

namespace App\Application\Order\Listener;

use App\Domain\Order\Repository\OrderRepository;
use App\Shared\Domain\DomainException;
use App\Shared\IntegrationEvents\WorkshopWorksCompleted;

final readonly class MarkOrderWorksCompletedOnWorkshopComplete
{
    public function __construct(
        private OrderRepository $orders,
    ) {}

    public function handle(WorkshopWorksCompleted $event): void
    {
        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            throw new DomainException('Order not found.');
        }

        $order->markWorksCompleted();
        $this->orders->save($order);
    }
}
