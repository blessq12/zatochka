<?php

namespace App\Application\OrderFulfillment\Query;

final readonly class GetPosOrderCountsQuery
{
    public function __construct(
        public int $masterId,
    ) {}
}
