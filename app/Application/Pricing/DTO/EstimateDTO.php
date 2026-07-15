<?php

namespace App\Application\Pricing\DTO;

final readonly class EstimateDTO
{
    public function __construct(
        public int $id,
        public int $orderItemId,
        public string $estimatedAmount,
        public string $currency,
        public ?int $itemPriceId,
        public ?string $finalAmount,
        public bool $calculated,
    ) {}
}
