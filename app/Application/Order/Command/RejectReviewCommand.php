<?php

namespace App\Application\Order\Command;

final readonly class RejectReviewCommand
{
    public function __construct(
        public int $reviewId,
        public int $moderatorId,
    ) {}
}
