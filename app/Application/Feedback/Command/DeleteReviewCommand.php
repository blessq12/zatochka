<?php

namespace App\Application\Feedback\Command;

final readonly class DeleteReviewCommand
{
    public function __construct(
        public int $reviewId,
    ) {}
}
