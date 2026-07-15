<?php

namespace App\Application\Feedback\Command;

final readonly class PublishReviewCommand
{
    public function __construct(
        public int $reviewId,
        public int $moderatorId,
        public ?string $managerReply = null,
    ) {}
}
