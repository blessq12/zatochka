<?php

namespace App\Domain\ClientPortal\Event;

use App\Domain\ClientPortal\Entity\Review;

final readonly class ReviewSubmitted
{
    public function __construct(
        public Review $review,
    ) {}
}
