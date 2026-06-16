<?php

namespace App\Application\Warehouse\Command;

final readonly class WriteOffStockCommand
{
    public function __construct(
        public int $warehouseItemId,
        public string $quantity,
        public ?string $comment = null,
        public ?int $userId = null,
    ) {}
}
