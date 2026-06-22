<?php

namespace App\Application\ClientPortal\QueryHandler;

use App\Application\ClientPortal\Query\GetLinkableGuestOrdersQuery;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class GetLinkableGuestOrdersQueryHandler
{
    public function __construct(
        private OrderRepositoryInterface $orders,
    ) {}

    /**
     * @return list<Order>
     */
    public function handle(GetLinkableGuestOrdersQuery $query): array
    {
        return $this->orders->searchGuestOrders($query->search, $query->limit);
    }
}
