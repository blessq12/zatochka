<?php

namespace App\Application\Finance\DTO;

final readonly class WorkPriceDTO
{
    public function __construct(
        public int $id,
        public int $performedWorkId,
        public int $orderItemId,
        public string $baseAmount,
        public string $currency,
        public bool $calculated,
        public ?string $finalAmount,
    ) {}
}
