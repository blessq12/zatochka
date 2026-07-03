<?php

namespace App\Application\OrderFulfillment\QueryHandler;

use App\Application\OrderFulfillment\Query\GetPosOrdersQuery;
use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class GetPosOrdersQueryHandler
{
    public function __construct(
        private OrderRepositoryInterface $orders,
    ) {}

    /**
     * @return array{items: list<Order>, total: int, page: int, per_page: int}
     */
    public function handle(GetPosOrdersQuery $query): array
    {
        $result = $this->orders->findForMaster(
            $query->masterId,
            $query->tab,
            $query->page,
            $query->perPage,
        );

        return [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $query->page,
            'per_page' => $query->perPage,
        ];
    }
}
