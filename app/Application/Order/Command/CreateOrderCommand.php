<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\CreateOrderItemDTO;

final readonly class CreateOrderCommand
{
    /**
     * @param list<CreateOrderItemDTO> $items
     */
    public function __construct(
        public int $orderId,
        public int $clientId,
        public string $estimatedAmount,
        public array $items,
        public string $estimatedCurrency = 'RUB',
    ) {}
}
