<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;

final readonly class RejectOrderItemUnitsHandler
{
    public function __construct(
        private OrderRepository $orders,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RejectOrderItemUnitsCommand $command): void
    {
        $order = $this->orders->getById(new OrderId($command->orderId));
        $item = $order->item(new EntityId($command->orderItemId));

        if ($item->quantity() === null) {
            $order->rejectEquipmentItem(new EntityId($command->orderItemId), $command->reason);
        } else {
            $order->rejectItemUnits(
                new EntityId($command->orderItemId),
                $command->quantity,
                $command->reason,
            );
        }

        $this->orders->save($order);
        $this->events->publish($order->pullDomainEvents());
    }
}
