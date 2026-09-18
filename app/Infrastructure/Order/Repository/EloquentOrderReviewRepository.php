<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Order\Aggregate\OrderReview;
use App\Domain\Order\Repository\OrderReviewRepository;
use App\Infrastructure\Order\Eloquent\OrderReviewModel;

final class EloquentOrderReviewRepository implements OrderReviewRepository
{
    public function save(OrderReview $review): OrderReview
    {
        $model = new OrderReviewModel([
            'order_id' => $review->orderId(),
            'client_id' => $review->clientId(),
            'rating' => $review->rating(),
            'text' => $review->text(),
        ]);
        $model->save();
        $review->assignId((int) $model->id);

        return $review;
    }

    public function findByOrderId(int $orderId): ?OrderReview
    {
        /** @var OrderReviewModel|null $model */
        $model = OrderReviewModel::query()->where('order_id', $orderId)->first();
        if ($model === null) {
            return null;
        }

        return new OrderReview(
            (int) $model->id,
            (int) $model->order_id,
            (int) $model->client_id,
            (int) $model->rating,
            $model->text,
        );
    }
}
