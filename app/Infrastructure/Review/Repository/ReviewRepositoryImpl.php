<?php

namespace App\Infrastructure\Review\Repository;

use App\Domain\Review\Entity\Review as ReviewEntity;
use App\Domain\Review\Mapper\ReviewMapper;
use App\Domain\Review\Repository\ReviewRepository;
use App\Models\Review;

class ReviewRepositoryImpl implements ReviewRepository
{
    public function __construct(
        private ReviewMapper $reviewMapper
    ) {}

    public function create(array $data): ReviewEntity
    {
        $model = Review::create($data);
        return $this->reviewMapper->toDomain($model);
    }

    public function get(string $id): ?ReviewEntity
    {
        $model = Review::find($id);
        return $model ? $this->reviewMapper->toDomain($model) : null;
    }

    public function update(ReviewEntity $review, array $data): ReviewEntity
    {
        $model = Review::find($review->getId());
        $model->update($data);
        return $this->reviewMapper->toDomain($model->fresh());
    }

    public function delete(string $id): bool
    {
        return Review::where('id', $id)->update(['is_deleted' => true]) > 0;
    }

    public function findByOrder(string $orderId): array
    {
        $models = Review::where('order_id', $orderId)
            ->where('is_deleted', false)
            ->get();

        return $models->map(fn($model) => $this->reviewMapper->toDomain($model))->toArray();
    }

    public function findByClient(string $clientId): array
    {
        $models = Review::where('client_id', $clientId)
            ->where('is_deleted', false)
            ->get();

        return $models->map(fn($model) => $this->reviewMapper->toDomain($model))->toArray();
    }

    public function getApprovedReviews(): array
    {
        $models = Review::where('is_approved', true)
            ->where('is_deleted', false)
            ->get();

        return $models->map(fn($model) => $this->reviewMapper->toDomain($model))->toArray();
    }

    public function existsByOrderAndClient(string $orderId, string $clientId): bool
    {
        return Review::where('order_id', $orderId)
            ->where('client_id', $clientId)
            ->where('is_deleted', false)
            ->exists();
    }
}
