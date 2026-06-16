<?php

namespace App\Application\OrderFulfillment\QueryHandler;

use App\Application\OrderFulfillment\Query\GetPosOrderCountsQuery;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class GetPosOrderCountsQueryHandler
{
    public function __construct(
        private OrderRepositoryInterface $orders,
    ) {}

    /** @return array<string, int> */
    public function handle(GetPosOrderCountsQuery $query): array
    {
        return $this->orders->countByTabForMaster($query->masterId);
    }
}
