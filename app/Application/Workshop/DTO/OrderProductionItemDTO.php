<?php

namespace App\Application\Workshop\DTO;

final readonly class OrderProductionItemDTO
{
    public function __construct(
        public int $orderItemId,
        public ?int $clientEquipmentId,
        public bool $fullyRejected,
    ) {}
}
