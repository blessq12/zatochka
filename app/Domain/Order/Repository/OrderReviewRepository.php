<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Aggregate\OrderReview;

interface OrderReviewRepository
{
    public function save(OrderReview $review): OrderReview;

    public function findByOrderId(int $orderId): ?OrderReview;
}
