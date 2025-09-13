<?php

namespace App\Domain\Review\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class ReviewApproved extends ShouldBeStored
{
    public function __construct(
        public readonly int $reviewId,
        public readonly ?int $approvedBy = null
    ) {}
}
