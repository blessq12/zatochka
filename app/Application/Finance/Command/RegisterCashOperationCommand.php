<?php

namespace App\Application\Finance\Command;

final readonly class RegisterCashOperationCommand
{
    public function __construct(
        public int $cashOperationId,
        public string $type,
        public string $amount,
        public string $currency = 'RUB',
        public ?string $comment = null,
        public ?int $paymentId = null,
        public ?int $refundId = null,
        public ?string $paymentMethod = null,
    ) {}
}
