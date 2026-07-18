<?php

namespace App\Infrastructure\Feedback\ReadModel;

use App\Application\Feedback\DTO\PublicReviewDTO;
use App\Application\Feedback\DTO\ReviewDTO;
use App\Application\Feedback\ReadPort\ReviewReadPort;
use App\Domain\Feedback\VO\ReviewStatus;
use App\Infrastructure\Feedback\Mapper\ReviewMapper;
use App\Infrastructure\Feedback\Model\ReviewModel;
use DateTimeInterface;

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

    public function findByOrderId(string $orderId): ?ReviewDTO
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

    public function listPublishedPublic(?int $limit = null): array
    {
        $query = ReviewModel::query()
            ->with('client')
            ->where('status', ReviewStatus::Published->value)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->orderByDesc('submitted_at');

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        return $query
            ->get()
            ->map(function (ReviewModel $model): PublicReviewDTO {
                $submittedAt = $model->submitted_at instanceof DateTimeInterface
                    ? $model->submitted_at->format(DateTimeInterface::ATOM)
                    : (string) $model->submitted_at;

                return new PublicReviewDTO(
                    (int) $model->id,
                    (int) $model->rating,
                    $model->comment !== null ? (string) $model->comment : null,
                    $model->manager_reply !== null ? (string) $model->manager_reply : null,
                    self::publicClientName($model->client?->name),
                    $submittedAt,
                );
            })
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

    private static function publicClientName(?string $name): string
    {
        $trimmed = trim((string) $name);

        if ($trimmed === '') {
            return 'Клиент';
        }

        $parts = preg_split('/\s+/u', $trimmed) ?: [];
        $first = $parts[0] ?? '';

        return $first !== '' ? $first : 'Клиент';
    }
}
