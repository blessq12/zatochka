<?php

namespace App\Application\Order\DTO;

final readonly class OrderDTO
{
    /**
     * @param list<OrderItemDTO> $items
     */
    public function __construct(
        public int $id,
        public int $clientId,
        public string $status,
        public string $estimatedAmount,
        public string $estimatedCurrency,
        public string $createdAt,
        public array $items,
    ) {}
}
