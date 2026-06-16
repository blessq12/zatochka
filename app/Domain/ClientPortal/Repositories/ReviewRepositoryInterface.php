<?php

namespace App\Domain\ClientPortal\Repositories;

use App\Domain\ClientPortal\Entities\Review;

interface ReviewRepositoryInterface
{
    public function findById(int $id): ?Review;

    public function save(Review $review): Review;
}
