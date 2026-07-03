<?php

namespace App\Application\ClientPortal\Query;

final readonly class GetClientOrderDetailQuery
{
    public function __construct(
        public int $clientId,
        public int $orderId,
    ) {}
}
