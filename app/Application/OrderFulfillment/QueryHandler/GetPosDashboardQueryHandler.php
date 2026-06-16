<?php

namespace App\Application\OrderFulfillment\QueryHandler;

use App\Application\OrderFulfillment\Query\GetPosDashboardQuery;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class GetPosDashboardQueryHandler
{
    public function __construct(
        private OrderRepositoryInterface $orders,
    ) {}

    /**
     * @return array{counts: array<string, int>, avg_work_duration_seconds: int|null}
     */
    public function handle(GetPosDashboardQuery $query): array
    {
        return [
            'counts' => $this->orders->countByTabForMaster($query->masterId),
            'avg_work_duration_seconds' => $this->orders->averageWorkDurationSecondsForMaster($query->masterId),
        ];
    }
}
