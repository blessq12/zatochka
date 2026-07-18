<?php

namespace App\Application\Finance\DTO;

final readonly class CashOperationListItemDTO
{
    public function __construct(
        public int $id,
        public string $type,
        public string $amount,
        public string $currency,
        public ?string $comment,
        public string $registeredAt,
        public ?int $paymentId,
        public ?int $refundId,
        public ?string $paymentMethod,
        public ?string $orderId = null,
        public ?string $orderNumber = null,
    ) {}
}
