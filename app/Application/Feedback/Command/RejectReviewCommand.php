<?php

namespace App\Application\Feedback\Command;

final readonly class RejectReviewCommand
{
    public function __construct(
        public int $reviewId,
        public int $moderatorId,
    ) {}
}
