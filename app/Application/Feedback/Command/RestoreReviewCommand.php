<?php

namespace App\Application\Feedback\Command;

final readonly class RestoreReviewCommand
{
    public function __construct(
        public int $reviewId,
    ) {}
}
