<?php

namespace App\Application\Order\Command;

final readonly class DeleteReviewCommand
{
    public function __construct(
        public int $reviewId,
    ) {}
}
