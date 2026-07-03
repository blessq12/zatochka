<?php

namespace App\Application\OrderFulfillment\QueryHandler;

use App\Application\OrderFulfillment\Query\GetPosOrderDetailQuery;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;

final class GetPosOrderDetailQueryHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
    ) {}

    public function handle(GetPosOrderDetailQuery $query): Order
    {
        $order = $this->orderLoader->load($query->orderId);

        if ($order->masterId() !== $query->masterId) {
            throw new OrderPolicyViolation('Заказ недоступен.');
        }

        return $order;
    }
}
