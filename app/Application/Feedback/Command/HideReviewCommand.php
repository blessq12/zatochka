<?php

namespace App\Application\Feedback\Command;

final readonly class HideReviewCommand
{
    public function __construct(
        public int $reviewId,
    ) {}
}
