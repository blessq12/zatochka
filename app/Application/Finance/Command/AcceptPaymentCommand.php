<?php

namespace App\Application\Finance\Command;

final readonly class AcceptPaymentCommand
{
    public function __construct(
        public int $paymentId,
        public string $orderId,
        public string $amount,
        public string $method,
        public string $currency = 'RUB',
    ) {}
}
