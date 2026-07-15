<?php

namespace App\Application\Pricing\Command;

final readonly class ApplyDiscountCommand
{
    public function __construct(
        public int $estimateId,
        public int $discountId,
        public string $type,
        public string $value,
        public ?string $reason = null,
    ) {}
}
