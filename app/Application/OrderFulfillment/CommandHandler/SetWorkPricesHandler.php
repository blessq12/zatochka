<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\SetWorkPricesCommand;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class SetWorkPricesHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(SetWorkPricesCommand $command): Order
    {
        $order = $this->orderLoader->load($command->orderId);

        foreach ($command->pricesBySortOrder as $sortOrder => $price) {
            $order = $order->setWorkPrice((int) $sortOrder, $price);
        }

        return $this->orders->save($order);
    }
}
