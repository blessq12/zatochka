<?php

namespace App\Infrastructure\Feedback\Repository;

use App\Domain\Feedback\Entity\Review;
use App\Domain\Feedback\Repository\ReviewRepository;
use App\Infrastructure\Feedback\Mapper\ReviewMapper;
use App\Infrastructure\Feedback\Model\ReviewModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class EloquentReviewRepository implements ReviewRepository
{
    public function __construct(
        private ReviewMapper $mapper,
    ) {}

    public function save(Review $review): void
    {
        $model = ReviewModel::query()->find($review->id()->value);
        $model = $this->mapper->toPersistence($review, $model);
        $model->save();
    }

    public function findById(EntityId $id): ?Review
    {
        $model = ReviewModel::query()->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): Review
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Review %d not found.', $id->value));
    }

    public function findByOrderId(EntityId $orderId): ?Review
    {
        $model = ReviewModel::query()->where('order_id', $orderId->value)->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }
}
