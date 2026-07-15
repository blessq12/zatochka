<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\Repository\OrderRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class CreateOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private DomainEventPublisher $events,
    ) {}

    public function handle(CreateOrderCommand $command): void
    {
        if ($command->items === []) {
            throw new DomainException('Order must contain at least one item.');
        }

        $items = [];

        foreach ($command->items as $itemDto) {
            $items[] = new OrderItem(
                new EntityId($itemDto->orderItemId),
                new EntityId($itemDto->clientEquipmentId),
            );
        }

        $order = Order::create(
            new EntityId($command->orderId),
            new EntityId($command->clientId),
            new Money($command->estimatedAmount, $command->estimatedCurrency),
            $items,
        );

        $this->orders->save($order);
        $this->events->publish($order->pullDomainEvents());
    }
}
