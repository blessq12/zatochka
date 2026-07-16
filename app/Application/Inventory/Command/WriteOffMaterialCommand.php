<?php

namespace App\Application\Inventory\Command;

final readonly class WriteOffMaterialCommand
{
    public function __construct(
        public int $stockItemId,
        public string $quantity,
        public ?string $comment = null,
        public ?string $orderId = null,
        public ?int $orderItemId = null,
        public ?int $movementId = null,
    ) {}
}
