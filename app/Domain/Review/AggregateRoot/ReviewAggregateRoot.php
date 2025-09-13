<?php

namespace App\Domain\Review\AggregateRoot;

use App\Domain\Review\Event\ReviewCreated;
use App\Domain\Review\Event\ReviewReplyAdded;
use App\Domain\Review\Event\ReviewApproved;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Illuminate\Support\Str;

class ReviewAggregateRoot extends AggregateRoot
{
    public function createReview(
        int $reviewId,
        int $clientId,
        int $orderId,
        int $rating,
        string $comment,
        array $metadata = []
    ): self {
        $this->recordThat(new ReviewCreated(
            reviewId: $reviewId,
            clientId: $clientId,
            orderId: $orderId,
            rating: $rating,
            comment: $comment,
            metadata: $metadata
        ));

        return $this;
    }

    public function addReply(int $reviewId, string $reply, ?int $repliedBy = null): self
    {
        $this->recordThat(new ReviewReplyAdded(
            reviewId: $reviewId,
            reply: $reply,
            repliedBy: $repliedBy
        ));

        return $this;
    }

    public function approveReview(int $reviewId, ?int $approvedBy = null): self
    {
        $this->recordThat(new ReviewApproved(
            reviewId: $reviewId,
            approvedBy: $approvedBy
        ));

        return $this;
    }

    public static function create(): self
    {
        return static::retrieve(Str::uuid()->toString());
    }
}
