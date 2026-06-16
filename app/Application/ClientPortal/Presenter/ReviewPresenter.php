<?php

namespace App\Application\ClientPortal\Presenter;

use App\Domain\ClientPortal\Entity\Review;

final class ReviewPresenter
{
    /** @return array<string, mixed> */
    public static function present(Review $review): array
    {
        return [
            'id' => $review->id(),
            'order_id' => $review->orderId(),
            'rating' => $review->rating(),
            'comment' => $review->comment(),
            'status' => $review->status()->value,
        ];
    }
}
