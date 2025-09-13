<?php

namespace App\Application\UseCases\Review;

use App\Domain\Review\Event\ReviewApproved;
use App\Domain\Review\Event\ReviewReplyAdded;

class ModerateReviewUseCase extends BaseReviewUseCase
{
    public function validateSpecificData(): self
    {
        if (!isset($this->data['id']) || empty($this->data['id'])) {
            throw new \InvalidArgumentException('Review ID is required');
        }

        if (!isset($this->data['action'])) {
            throw new \InvalidArgumentException('Action is required (approve, reject, reply)');
        }

        $action = $this->data['action'];
        if (!in_array($action, ['approve', 'reject', 'reply'])) {
            throw new \InvalidArgumentException('Action must be approve, reject or reply');
        }

        if ($action === 'reply' && empty($this->data['reply'])) {
            throw new \InvalidArgumentException('Reply text is required for reply action');
        }

        return $this;
    }

    public function execute(): mixed
    {
        $review = $this->reviewRepository->get($this->data['id']);
        if (!$review) {
            throw new \InvalidArgumentException('Review not found');
        }

        $aggregate = \App\Domain\Review\AggregateRoot\ReviewAggregateRoot::create();

        switch ($this->data['action']) {
            case 'approve':
                $updatedReview = $review->approve();
                $aggregate->approveReview(
                    reviewId: (int) $review->getId(),
                    approvedBy: $this->data['approved_by'] ?? null
                );
                $review = $updatedReview;
                break;

            case 'reject':
                $review = $review->reject();
                break;

            case 'reply':
                $updatedReview = $review->addReply($this->data['reply']);
                $aggregate->addReply(
                    reviewId: (int) $review->getId(),
                    reply: $this->data['reply'],
                    repliedBy: $this->data['replied_by'] ?? null
                );
                $review = $updatedReview;
                break;
        }

        $aggregate->persist();
        return $this->reviewRepository->update($review, $review->toArray());
    }
}
