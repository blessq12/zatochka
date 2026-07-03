<?php

namespace App\Application\OrderFulfillment\Query;

final readonly class GetPosOrderDetailQuery
{
    public function __construct(
        public int $orderId,
        public int $masterId,
    ) {}
}
