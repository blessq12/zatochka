<?php

namespace App\Application\Pricing\Command;

use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Shared\Domain\DomainException;

final readonly class SetOrderPricesHandler
{
    public function __construct(
        private OrderRepository $orders,
        private SetOrderItemPriceHandler $setItemPrice,
    ) {}

    public function handle(SetOrderPricesCommand $command): void
    {
        $order = $this->orders->getById(new OrderId($command->orderId));

        if ($order->status() !== OrderStatus::WorksCompleted) {
            throw new DomainException('Prices can only be changed while order is awaiting pricing.');
        }

        if ($command->items === []) {
            throw new DomainException('At least one item price is required.');
        }

        foreach ($command->items as $item) {
            $this->setItemPrice->handle(new SetOrderItemPriceCommand(
                (int) $item['order_item_id'],
                (string) $item['base_amount'],
                $command->currency,
            ));
        }
    }
}
