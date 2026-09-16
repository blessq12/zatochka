<?php

namespace App\Application\Order\Command;

final readonly class RestoreReviewCommand
{
    public function __construct(
        public int $reviewId,
    ) {}
}
