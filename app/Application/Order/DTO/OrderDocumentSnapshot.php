<?php

namespace App\Application\Order\DTO;

final readonly class OrderDocumentSnapshot
{
    /**
     * @param array<string, string> $placeholders
     */
    public function __construct(
        public string $orderId,
        public string $orderNumber,
        public string $status,
        public array $placeholders,
    ) {}
}
