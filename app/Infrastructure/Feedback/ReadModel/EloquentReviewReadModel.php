<?php

namespace App\Infrastructure\Feedback\ReadModel;

use App\Application\Feedback\DTO\ReviewDTO;
use App\Application\Feedback\ReadPort\ReviewReadPort;
use App\Domain\Feedback\VO\ReviewStatus;
use App\Infrastructure\Feedback\Mapper\ReviewMapper;
use App\Infrastructure\Feedback\Model\ReviewModel;

final readonly class EloquentReviewReadModel implements ReviewReadPort
{
    public function __construct(
        private ReviewMapper $mapper,
    ) {}

    public function findById(int $reviewId): ?ReviewDTO
    {
        $model = ReviewModel::query()->find($reviewId);

        return $model === null ? null : $this->mapper->toDTO($model);
    }

    public function findByOrderId(int $orderId): ?ReviewDTO
    {
        $model = ReviewModel::query()->where('order_id', $orderId)->first();

        return $model === null ? null : $this->mapper->toDTO($model);
    }

    public function listPending(): array
    {
        return ReviewModel::query()
            ->where('status', ReviewStatus::PendingModeration->value)
            ->orderBy('submitted_at')
            ->get()
            ->map(fn (ReviewModel $model): ReviewDTO => $this->mapper->toDTO($model))
            ->all();
    }

    public function listPublished(): array
    {
        return ReviewModel::query()
            ->where('status', ReviewStatus::Published->value)
            ->orderByDesc('submitted_at')
            ->get()
            ->map(fn (ReviewModel $model): ReviewDTO => $this->mapper->toDTO($model))
            ->all();
    }

    public function averagePublishedRating(): ?string
    {
        $average = ReviewModel::query()
            ->where('status', ReviewStatus::Published->value)
            ->avg('rating');

        if ($average === null) {
            return null;
        }

        return number_format((float) $average, 2, '.', '');
    }
}
