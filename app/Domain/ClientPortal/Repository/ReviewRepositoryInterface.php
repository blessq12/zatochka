<?php

namespace App\Domain\ClientPortal\Repository;

use App\Domain\ClientPortal\Entity\Review;

interface ReviewRepositoryInterface
{
    public function findById(int $id): ?Review;

    public function save(Review $review): Review;
}
