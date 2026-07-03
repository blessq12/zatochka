<?php

namespace App\Application\ClientPortal\QueryHandler;

use App\Application\ClientPortal\Query\GetClientOrderDetailQuery;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Exception\OrderNotFoundException;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class GetClientOrderDetailQueryHandler
{
    public function __construct(
        private OrderRepositoryInterface $orders,
    ) {}

    public function handle(GetClientOrderDetailQuery $query): Order
    {
        $order = $this->orders->findByIdForClient($query->orderId, $query->clientId);

        if ($order === null) {
            throw OrderNotFoundException::withId($query->orderId);
        }

        return $order;
    }
}
