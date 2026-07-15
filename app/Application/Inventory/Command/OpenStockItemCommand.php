<?php

namespace App\Application\Inventory\Command;

final readonly class OpenStockItemCommand
{
    public function __construct(
        public int $stockItemId,
        public int $materialId,
        public string $name,
        public string $unit,
        public string $category,
        public string $initialQuantity = '0',
    ) {}
}
