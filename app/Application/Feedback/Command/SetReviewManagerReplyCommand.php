<?php

namespace App\Application\Feedback\Command;

final readonly class SetReviewManagerReplyCommand
{
    public function __construct(
        public int $reviewId,
        public string $managerReply,
    ) {}
}
