<?php

namespace App\Domain\OrderFulfillment\Entity;

final readonly class OrderMaterial
{
    public function __construct(
        public ?int $id,
        public int $warehouseItemId,
        public string $quantity,
        public string $unitPrice,
        public string $totalPrice,
    ) {}
}
