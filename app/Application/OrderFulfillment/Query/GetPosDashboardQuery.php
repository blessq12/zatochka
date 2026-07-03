<?php

namespace App\Application\OrderFulfillment\Query;

final readonly class GetPosDashboardQuery
{
    public function __construct(
        public int $masterId,
    ) {}
}
