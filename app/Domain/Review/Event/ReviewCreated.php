<?php

namespace App\Domain\Review\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class ReviewCreated extends ShouldBeStored
{
    public function __construct(
        public readonly int $reviewId,
        public readonly int $clientId,
        public readonly int $orderId,
        public readonly int $rating,
        public readonly string $comment,
        public readonly array $metadata = []
    ) {}
}
