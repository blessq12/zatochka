<?php

namespace App\Application\OrderFulfillment\Support;

use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Exception\OrderNotFoundException;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class OrderLoader
{
    public function __construct(
        private OrderRepositoryInterface $orders,
    ) {}

    public function load(int $orderId): Order
    {
        $order = $this->orders->findById($orderId);

        if ($order === null) {
            throw OrderNotFoundException::withId($orderId);
        }

        return $order;
    }
}
