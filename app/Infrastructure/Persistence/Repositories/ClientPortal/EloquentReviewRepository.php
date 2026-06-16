<?php

namespace App\Infrastructure\Persistence\Repositories\ClientPortal;

use App\Domain\ClientPortal\Entities\Review;
use App\Domain\ClientPortal\Repositories\ReviewRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ClientPortal\ReviewModel;
use App\Infrastructure\Persistence\Mappers\ClientPortal\ReviewMapper;

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
