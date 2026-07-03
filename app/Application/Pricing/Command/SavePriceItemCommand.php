<?php

namespace App\Application\Pricing\Command;

use App\Domain\Pricing\Enum\PricePrefix;

final readonly class SavePriceItemCommand
{
    public function __construct(
        public ?int $id,
        public int $priceBlockId,
        public string $name,
        public string $price,
        public ?PricePrefix $pricePrefix,
        public ?string $description,
        public int $sortOrder,
    ) {}
}
