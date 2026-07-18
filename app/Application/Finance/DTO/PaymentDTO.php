<?php

namespace App\Application\Finance\DTO;

final readonly class PaymentDTO
{
    public function __construct(
        public int $id,
        public string $number,
        public string $orderId,
        public string $amount,
        public string $currency,
        public string $method,
        public string $acceptedAt,
    ) {}
}
