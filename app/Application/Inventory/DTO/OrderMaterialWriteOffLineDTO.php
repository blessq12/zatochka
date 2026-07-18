<?php

namespace App\Application\Inventory\DTO;

final readonly class OrderMaterialWriteOffLineDTO
{
    public function __construct(
        public int $movementId,
        public int $stockItemId,
        public string $quantity,
        public string $unitPrice,
        public string $currency,
        public ?int $orderItemId = null,
        public ?string $comment = null,
        public ?string $materialName = null,
    ) {}
}
