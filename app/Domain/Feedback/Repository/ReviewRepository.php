<?php

namespace App\Domain\Feedback\Repository;

use App\Domain\Feedback\Entity\Review;
use App\Shared\ValueObject\EntityId;

interface ReviewRepository
{
    public function save(Review $review): void;

    public function findById(EntityId $id): ?Review;

    public function getById(EntityId $id): Review;

    public function findByOrderId(EntityId $orderId): ?Review;
}
