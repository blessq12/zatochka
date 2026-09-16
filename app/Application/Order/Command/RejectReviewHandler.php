<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\ReviewRepository;
use App\Shared\ValueObject\EntityId;

final readonly class RejectReviewHandler
{
    public function __construct(
        private ReviewRepository $reviews,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RejectReviewCommand $command): void
    {
        $review = $this->reviews->getById(new EntityId($command->reviewId));
        $review->reject(new EntityId($command->moderatorId));
        $this->reviews->save($review);
        $this->events->publish($review->pullDomainEvents());
    }
}
