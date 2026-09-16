<?php

namespace App\Application\Order\Command;

final readonly class HideReviewCommand
{
    public function __construct(
        public int $reviewId,
    ) {}
}
