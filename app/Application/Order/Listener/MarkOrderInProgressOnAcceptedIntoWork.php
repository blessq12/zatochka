<?php

namespace App\Application\Order\Listener;

use App\Domain\Order\Repository\OrderRepository;
use App\Shared\Domain\DomainException;
use App\Shared\IntegrationEvents\OrderAcceptedIntoWork;

final readonly class MarkOrderInProgressOnAcceptedIntoWork
{
    public function __construct(
        private OrderRepository $orders,
    ) {}

    public function handle(OrderAcceptedIntoWork $event): void
    {
        $order = $this->orders->findById($event->orderId);
        if ($order === null) {
            throw new DomainException('Order not found.');
        }

        $order->markAcceptedIntoWork($event->masterId);
        $this->orders->save($order);
    }
}
