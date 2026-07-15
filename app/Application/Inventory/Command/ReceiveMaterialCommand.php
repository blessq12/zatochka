<?php

namespace App\Application\Inventory\Command;

final readonly class ReceiveMaterialCommand
{
    public function __construct(
        public int $stockItemId,
        public int $movementId,
        public string $quantity,
        public ?string $comment = null,
    ) {}
}
