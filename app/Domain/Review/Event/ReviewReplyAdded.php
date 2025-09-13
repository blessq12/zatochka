<?php

namespace App\Domain\Review\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class ReviewReplyAdded extends ShouldBeStored
{
    public function __construct(
        public readonly int $reviewId,
        public readonly string $reply,
        public readonly ?int $repliedBy = null
    ) {}
}
