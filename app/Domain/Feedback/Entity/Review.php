<?php

namespace App\Domain\Feedback\Entity;

use App\Domain\Feedback\Event\ReviewDeleted;
use App\Domain\Feedback\Event\ReviewHidden;
use App\Domain\Feedback\Event\ReviewPublished;
use App\Domain\Feedback\Event\ReviewRejected;
use App\Domain\Feedback\Event\ReviewRestored;
use App\Domain\Feedback\Event\ReviewSubmitted;
use App\Domain\Feedback\VO\Rating;
use App\Domain\Feedback\VO\ReviewStatus;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;
use DateTimeImmutable;

final class Review extends AggregateRoot
{
    private ReviewStatus $status;
    private ?string $managerReply = null;
    private ?EntityId $moderatedBy = null;
    private ?DateTimeImmutable $moderatedAt = null;
    private ?DateTimeImmutable $hiddenAt = null;
    private ?DateTimeImmutable $deletedAt = null;

    private function __construct(
        private readonly EntityId $id,
        private readonly OrderId $orderId,
        private readonly EntityId $clientId,
        private readonly Rating $rating,
        private readonly ?string $comment,
        private readonly DateTimeImmutable $submittedAt,
    ) {
        $this->status = ReviewStatus::PendingModeration;
    }

    public static function submit(
        EntityId $id,
        OrderId $orderId,
        EntityId $clientId,
        Rating $rating,
        ?string $comment = null,
        ?DateTimeImmutable $submittedAt = null,
    ): self {
        $normalizedComment = self::normalizeText($comment);

        $review = new self(
            $id,
            $orderId,
            $clientId,
            $rating,
            $normalizedComment,
            $submittedAt ?? new DateTimeImmutable(),
        );
        $review->record(new ReviewSubmitted($id, $orderId, $clientId));

        return $review;
    }

    public static function reconstitute(
        EntityId $id,
        OrderId $orderId,
        EntityId $clientId,
        Rating $rating,
        ?string $comment,
        ReviewStatus $status,
        DateTimeImmutable $submittedAt,
        ?string $managerReply = null,
        ?EntityId $moderatedBy = null,
        ?DateTimeImmutable $moderatedAt = null,
        ?DateTimeImmutable $hiddenAt = null,
        ?DateTimeImmutable $deletedAt = null,
    ): self {
        $review = new self($id, $orderId, $clientId, $rating, $comment, $submittedAt);
        $review->status = $status;
        $review->managerReply = $managerReply;
        $review->moderatedBy = $moderatedBy;
        $review->moderatedAt = $moderatedAt;
        $review->hiddenAt = $hiddenAt;
        $review->deletedAt = $deletedAt;

        return $review;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function orderId(): OrderId
    {
        return $this->orderId;
    }

    public function clientId(): EntityId
    {
        return $this->clientId;
    }

    public function rating(): Rating
    {
        return $this->rating;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function managerReply(): ?string
    {
        return $this->managerReply;
    }

    public function status(): ReviewStatus
    {
        return $this->status;
    }

    public function moderatedBy(): ?EntityId
    {
        return $this->moderatedBy;
    }

    public function submittedAt(): DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function moderatedAt(): ?DateTimeImmutable
    {
        return $this->moderatedAt;
    }

    public function hiddenAt(): ?DateTimeImmutable
    {
        return $this->hiddenAt;
    }

    public function deletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function publish(EntityId $moderatorId, ?string $managerReply = null): void
    {
        $this->transitionTo(ReviewStatus::Published);
        $this->managerReply = self::normalizeText($managerReply);
        $this->moderatedBy = $moderatorId;
        $this->moderatedAt = new DateTimeImmutable();
        $this->hiddenAt = null;
        $this->record(new ReviewPublished($this->id, $this->orderId, $moderatorId));
    }

    public function reject(EntityId $moderatorId): void
    {
        $this->transitionTo(ReviewStatus::Rejected);
        $this->moderatedBy = $moderatorId;
        $this->moderatedAt = new DateTimeImmutable();
        $this->record(new ReviewRejected($this->id, $this->orderId, $moderatorId));
    }

    public function setManagerReply(string $managerReply): void
    {
        if (! in_array($this->status, [ReviewStatus::PendingModeration, ReviewStatus::Published], true)) {
            throw new DomainException('Manager reply can be set only for pending or published reviews.');
        }

        $normalized = self::normalizeText($managerReply);

        if ($normalized === null) {
            throw new DomainException('Manager reply is required.');
        }

        $this->managerReply = $normalized;
    }

    public function hide(): void
    {
        $this->transitionTo(ReviewStatus::Hidden);
        $this->hiddenAt = new DateTimeImmutable();
        $this->record(new ReviewHidden($this->id, $this->orderId));
    }

    public function restore(): void
    {
        $this->transitionTo(ReviewStatus::Published);
        $this->hiddenAt = null;
        $this->record(new ReviewRestored($this->id, $this->orderId));
    }

    public function delete(): void
    {
        $this->transitionTo(ReviewStatus::Deleted);
        $this->deletedAt = new DateTimeImmutable();
        $this->record(new ReviewDeleted($this->id, $this->orderId));
    }

    private function transitionTo(ReviewStatus $next): void
    {
        if (! $this->status->canTransitionTo($next)) {
            throw new DomainException(sprintf(
                'Review status transition from %s to %s is not allowed.',
                $this->status->value,
                $next->value,
            ));
        }

        $this->status = $next;
    }

    private static function normalizeText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $trimmed = trim($text);

        return $trimmed === '' ? null : $trimmed;
    }
}
