<?php

namespace App\Application\Pricing\DTO;

final readonly class WorkPriceDTO
{
    public function __construct(
        public int $id,
        public int $masterCommentId,
        public int $orderItemId,
        public string $baseAmount,
        public string $currency,
        public bool $calculated,
        public ?string $finalAmount,
    ) {}
}
