<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\CreateOrderItemDTO;

final readonly class CreateOrderCommand
{
    /**
     * @param list<CreateOrderItemDTO> $items
     */
    public function __construct(
        public string $orderId,
        public int $clientId,
        public string $estimatedAmount,
        public array $items,
        public string $serviceType,
        public string $billingType,
        public string $urgency,
        public bool $deliveryRequired = false,
        public ?string $defects = null,
        public ?string $internalNotes = null,
        public string $estimatedCurrency = 'RUB',
        public ?string $warrantySourceOrderId = null,
        public ?string $clientComment = null,
        public string $source = 'admin',
    ) {}
}
