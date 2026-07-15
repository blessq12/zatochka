<?php

namespace App\Application\Delivery\Query;

final readonly class GetDeliveryRequestByIdQuery
{
    public function __construct(
        public int $deliveryRequestId,
    ) {}
}
