<?php

namespace App\Application\Inventory\Command;

final readonly class SetMaterialUnitPriceCommand
{
    public function __construct(
        public int $stockItemId,
        public string $unitPrice,
        public string $currency = 'RUB',
    ) {}
}
