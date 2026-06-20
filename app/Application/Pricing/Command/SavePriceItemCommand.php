<?php

namespace App\Application\Pricing\Command;

final readonly class SavePriceItemCommand
{
    public function __construct(
        public ?int $id,
        public int $priceBlockId,
        public string $name,
        public string $price,
        public ?string $description,
        public int $sortOrder,
    ) {}
}
