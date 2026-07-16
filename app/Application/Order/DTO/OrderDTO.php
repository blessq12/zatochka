<?php

namespace App\Application\Order\DTO;

final readonly class OrderDTO
{
    /**
     * @param list<OrderItemDTO> $items
     */
    public function __construct(
        public string $id,
        public string $number,
        public int $clientId,
        public string $status,
        public string $serviceType,
        public string $billingType,
        public string $urgency,
        public bool $deliveryRequired,
        public ?string $defects,
        public ?string $internalNotes,
        public ?string $warrantySourceOrderId,
        public ?int $assignedMasterId,
        public string $estimatedAmount,
        public string $estimatedCurrency,
        public string $createdAt,
        public array $items,
    ) {}
}
