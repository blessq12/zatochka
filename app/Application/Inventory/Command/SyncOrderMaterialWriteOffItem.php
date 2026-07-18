<?php

namespace App\Application\Inventory\Command;

final readonly class SyncOrderMaterialWriteOffItem
{
    public function __construct(
        public int $stockItemId,
        public string $quantity,
        public string $unitPrice,
        public ?int $movementId = null,
        public ?int $orderItemId = null,
        public ?string $comment = null,
        public string $currency = 'RUB',
    ) {}
}
