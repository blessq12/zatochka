<?php

namespace App\Domain\ClientPortal\Entity;

use App\Domain\ClientPortal\Enum\ReviewStatus;
use App\Domain\ClientPortal\Exception\ReviewPolicyViolation;

final class Review
{
    public function __construct(
        private ?int $id,
        private int $orderId,
        private int $clientId,
        private int $rating,
        private ?string $comment,
        private ReviewStatus $status,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function clientId(): int
    {
        return $this->clientId;
    }

    public function rating(): int
    {
        return $this->rating;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function status(): ReviewStatus
    {
        return $this->status;
    }

    public static function submit(
        int $orderId,
        int $clientId,
        int $rating,
        ?string $comment,
    ): self {
        if ($rating < 1 || $rating > 5) {
            throw new ReviewPolicyViolation('Оценка должна быть от 1 до 5.');
        }

        return new self(
            id: null,
            orderId: $orderId,
            clientId: $clientId,
            rating: $rating,
            comment: $comment,
            status: ReviewStatus::Pending,
        );
    }

    public function approve(): self
    {
        if ($this->status !== ReviewStatus::Pending) {
            throw new ReviewPolicyViolation('Опубликовать можно только отзыв на модерации.');
        }

        $clone = clone $this;
        $clone->status = ReviewStatus::Approved;

        return $clone;
    }

    public function reject(): self
    {
        if ($this->status !== ReviewStatus::Pending) {
            throw new ReviewPolicyViolation('Отклонить можно только отзыв на модерации.');
        }

        $clone = clone $this;
        $clone->status = ReviewStatus::Rejected;

        return $clone;
    }

    public function assignId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }
}
