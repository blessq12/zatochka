<?php

namespace App\Application\Inventory\Command;

final readonly class ChangeStockCommand
{
    public function __construct(
        public int $stockItemId,
        public int $movementId,
        public string $quantity,
        public ?string $comment = null,
    ) {}
}
