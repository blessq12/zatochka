<?php

namespace App\Application\ClientPortal\CommandHandler;

use App\Application\ClientPortal\Command\ApproveReviewCommand;
use App\Domain\ClientPortal\Entity\Review;
use App\Domain\ClientPortal\Event\ReviewApproved;
use App\Domain\ClientPortal\Exception\ReviewPolicyViolation;
use App\Domain\ClientPortal\Repository\ReviewRepositoryInterface;

final class ApproveReviewHandler
{
    public function __construct(
        private ReviewRepositoryInterface $reviews,
    ) {}

    public function handle(ApproveReviewCommand $command): Review
    {
        $review = $this->reviews->findById($command->reviewId);

        if ($review === null) {
            throw new ReviewPolicyViolation('Отзыв не найден.');
        }

        if ($command->clientId !== null && $review->clientId() !== $command->clientId) {
            throw new ReviewPolicyViolation('Отзыв не принадлежит этому клиенту.');
        }

        $saved = $this->reviews->save($review->approve());

        event(new ReviewApproved($saved));

        return $saved;
    }
}
