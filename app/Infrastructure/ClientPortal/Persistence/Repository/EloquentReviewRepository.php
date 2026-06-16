<?php

namespace App\Infrastructure\ClientPortal\Persistence\Repository;

use App\Domain\ClientPortal\Entity\Review;
use App\Domain\ClientPortal\Repository\ReviewRepositoryInterface;
use App\Infrastructure\ClientPortal\Persistence\Eloquent\ReviewModel;
use App\Infrastructure\ClientPortal\Persistence\Mapper\ReviewMapper;

final class EloquentReviewRepository implements ReviewRepositoryInterface
{
    public function __construct(
        private ReviewMapper $mapper,
    ) {}

    public function findById(int $id): ?Review
    {
        $model = ReviewModel::query()->find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findByOrderId(int $orderId): ?Review
    {
        $model = ReviewModel::query()->where('order_id', $orderId)->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(Review $review): Review
    {
        $model = $review->id() !== null
            ? ReviewModel::query()->findOrFail($review->id())
            : new ReviewModel;

        $this->mapper->fillModel($review, $model);
        $model->save();

        return $this->mapper->toDomain($model);
    }
}
