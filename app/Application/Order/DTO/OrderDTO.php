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
        public string $serviceType,
        public string $billingType,
        public string $urgency,
        public bool $deliveryRequired,
        public ?string $defects,
        public ?string $internalNotes,
        public ?int $warrantySourceOrderId,
        public string $estimatedAmount,
        public string $estimatedCurrency,
        public string $createdAt,
        public array $items,
    ) {}
}
