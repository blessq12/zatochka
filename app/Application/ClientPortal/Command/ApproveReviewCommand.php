<?php

namespace App\Application\ClientPortal\Command;

final readonly class ApproveReviewCommand
{
    public function __construct(
        public int $reviewId,
        public ?int $clientId = null,
    ) {}
}
