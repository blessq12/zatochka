<?php

namespace App\Application\Order\DTO;

final readonly class OrderItemDTO
{
    public function __construct(
        public int $id,
        public ?int $clientEquipmentId,
        public ?string $toolName,
        public ?string $toolType,
        public ?int $quantity,
        public int $rejectedQuantity,
        public ?string $rejectionReason,
        public int $repairableQuantity,
        public string $status,
        public bool $hasReception,
        public ?int $warrantyId,
        public ?string $equipmentTitle = null,
    ) {}
}
