<?php

namespace App\Application\Finance\Query;

final readonly class GetPaymentByIdQuery
{
    public function __construct(
        public int $paymentId,
    ) {}
}
