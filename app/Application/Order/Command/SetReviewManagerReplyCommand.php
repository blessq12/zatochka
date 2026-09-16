<?php

namespace App\Application\Order\Command;

final readonly class SetReviewManagerReplyCommand
{
    public function __construct(
        public int $reviewId,
        public string $managerReply,
    ) {}
}
