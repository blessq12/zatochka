<?php

namespace App\Application\Finance\Command;

final readonly class RecordPaymentForIssuedOrderCommand
{
    public function __construct(
        public string $orderId,
        public ?string $paymentMethod,
    ) {}
}
