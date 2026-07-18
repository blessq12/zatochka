<?php

namespace App\Application\Inventory\Command;

final readonly class ReverseOrderMaterialWriteOffCommand
{
    public function __construct(
        public int $stockItemId,
        public int $writeOffMovementId,
        public ?int $reversalMovementId = null,
        public ?string $comment = null,
    ) {}
}
