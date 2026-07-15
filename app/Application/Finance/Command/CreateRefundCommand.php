<?php

namespace App\Application\Finance\Command;

final readonly class CreateRefundCommand
{
    public function __construct(
        public int $paymentId,
        public int $refundId,
        public string $amount,
        public string $currency = 'RUB',
        public ?string $reason = null,
    ) {}
}
