<?php

namespace App\Infrastructure\Feedback\Mapper;

use App\Application\Feedback\DTO\ReviewDTO;
use App\Domain\Feedback\Entity\Review;
use App\Domain\Feedback\VO\Rating;
use App\Domain\Feedback\VO\ReviewStatus;
use App\Infrastructure\Feedback\Model\ReviewModel;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;
use DateTimeInterface;

final class ReviewMapper
{
    public function toDomain(ReviewModel $model): Review
    {
        return Review::reconstitute(
            new EntityId((int) $model->id),
            new OrderId((string) $model->order_id),
            new EntityId((int) $model->client_id),
            new Rating((int) $model->rating),
            $model->comment !== null ? (string) $model->comment : null,
            ReviewStatus::from((string) $model->status),
            DateTimeImmutable::createFromInterface($model->submitted_at),
            $model->manager_reply !== null ? (string) $model->manager_reply : null,
            $model->moderated_by !== null ? new EntityId((int) $model->moderated_by) : null,
            $model->moderated_at !== null
                ? DateTimeImmutable::createFromInterface($model->moderated_at)
                : null,
            $model->hidden_at !== null
                ? DateTimeImmutable::createFromInterface($model->hidden_at)
                : null,
            $model->deleted_at !== null
                ? DateTimeImmutable::createFromInterface($model->deleted_at)
                : null,
        );
    }

    public function toPersistence(Review $review, ?ReviewModel $model = null): ReviewModel
    {
        $model ??= new ReviewModel();
        $model->id = $review->id()->value;
        $model->order_id = $review->orderId()->value;
        $model->client_id = $review->clientId()->value;
        $model->rating = $review->rating()->value;
        $model->comment = $review->comment();
        $model->manager_reply = $review->managerReply();
        $model->status = $review->status()->value;
        $model->moderated_by = $review->moderatedBy()?->value;
        $model->submitted_at = $review->submittedAt();
        $model->moderated_at = $review->moderatedAt();
        $model->hidden_at = $review->hiddenAt();
        $model->deleted_at = $review->deletedAt();

        return $model;
    }

    public function toDTO(ReviewModel $model): ReviewDTO
    {
        return new ReviewDTO(
            (int) $model->id,
            (string) $model->order_id,
            (int) $model->client_id,
            (int) $model->rating,
            $model->comment !== null ? (string) $model->comment : null,
            $model->manager_reply !== null ? (string) $model->manager_reply : null,
            (string) $model->status,
            $model->moderated_by !== null ? (int) $model->moderated_by : null,
            $this->formatDate($model->submitted_at),
            $model->moderated_at !== null ? $this->formatDate($model->moderated_at) : null,
            $model->hidden_at !== null ? $this->formatDate($model->hidden_at) : null,
            $model->deleted_at !== null ? $this->formatDate($model->deleted_at) : null,
        );
    }

    private function formatDate(DateTimeInterface $date): string
    {
        return $date->format(DateTimeInterface::ATOM);
    }
}
