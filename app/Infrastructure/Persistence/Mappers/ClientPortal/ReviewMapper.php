<?php

namespace App\Infrastructure\Persistence\Mappers\ClientPortal;

use App\Domain\ClientPortal\Entities\Review;
use App\Infrastructure\Persistence\Eloquent\Models\ClientPortal\ReviewModel;

final class ReviewMapper
{
    public function toDomain(ReviewModel $model): Review
    {
        return new Review(
            id: $model->id,
            orderId: $model->order_id,
            clientId: $model->client_id,
            rating: $model->rating,
            comment: $model->comment,
            status: $model->status,
        );
    }

    public function fillModel(Review $review, ReviewModel $model): void
    {
        $model->fill([
            'order_id' => $review->orderId(),
            'client_id' => $review->clientId(),
            'rating' => $review->rating(),
            'comment' => $review->comment(),
            'status' => $review->status(),
        ]);
    }
}
