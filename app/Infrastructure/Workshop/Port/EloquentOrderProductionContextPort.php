<?php

namespace App\Infrastructure\Workshop\Port;

use App\Application\Workshop\Port\OrderProductionContextPort;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;

final readonly class EloquentOrderProductionContextPort implements OrderProductionContextPort
{
    public function __construct(
        private OrderRepository $orders,
    ) {}

    public function getById(OrderId $orderId): Order
    {
        return $this->orders->getById($orderId);
    }
}
