<?php

namespace App\Domain\ClientPortal\Event;

use App\Domain\ClientPortal\Entity\Review;

final readonly class ReviewApproved
{
    public function __construct(
        public Review $review,
    ) {}
}
