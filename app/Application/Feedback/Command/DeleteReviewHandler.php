<?php

namespace App\Application\Feedback\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Feedback\Repository\ReviewRepository;
use App\Shared\ValueObject\EntityId;

final readonly class DeleteReviewHandler
{
    public function __construct(
        private ReviewRepository $reviews,
        private DomainEventPublisher $events,
    ) {}

    public function handle(DeleteReviewCommand $command): void
    {
        $review = $this->reviews->getById(new EntityId($command->reviewId));
        $review->delete();
        $this->reviews->save($review);
        $this->events->publish($review->pullDomainEvents());
    }
}
