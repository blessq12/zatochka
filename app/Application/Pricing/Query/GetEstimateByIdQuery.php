<?php

namespace App\Application\Pricing\Query;

final readonly class GetEstimateByIdQuery
{
    public function __construct(
        public int $estimateId,
    ) {}
}
