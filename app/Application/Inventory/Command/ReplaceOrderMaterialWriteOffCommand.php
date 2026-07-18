<?php

namespace App\Application\Inventory\Command;

final readonly class ReplaceOrderMaterialWriteOffCommand
{
    public function __construct(
        public int $stockItemId,
        public int $writeOffMovementId,
        public string $quantity,
        public string $unitPrice,
        public string $currency = 'RUB',
        public ?int $orderItemId = null,
        public ?string $comment = null,
        public ?int $reversalMovementId = null,
        public ?int $newWriteOffMovementId = null,
    ) {}
}
