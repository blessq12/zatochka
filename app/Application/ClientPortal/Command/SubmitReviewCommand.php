<?php

namespace App\Application\ClientPortal\Command;

final readonly class SubmitReviewCommand
{
    public function __construct(
        public int $clientId,
        public int $orderId,
        public int $rating,
        public ?string $comment = null,
    ) {}
}
