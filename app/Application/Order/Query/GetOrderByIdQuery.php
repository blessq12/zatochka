<?php

namespace App\Application\Order\Query;

final readonly class GetOrderByIdQuery
{
    public function __construct(
        public string $orderId,
    ) {}
}
