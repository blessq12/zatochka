<?php

namespace App\Application\Inventory\DTO;

final readonly class StockItemDTO
{
    public function __construct(
        public int $id,
        public int $materialId,
        public string $materialSku,
        public string $materialName,
        public string $unit,
        public string $category,
        public string $quantityOnHand,
        public string $unitPrice = '0.00',
        public string $currency = 'RUB',
    ) {}
}
