<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\ReviewRepository;
use App\Shared\ValueObject\EntityId;

final readonly class RestoreReviewHandler
{
    public function __construct(
        private ReviewRepository $reviews,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RestoreReviewCommand $command): void
    {
        $review = $this->reviews->getById(new EntityId($command->reviewId));
        $review->restore();
        $this->reviews->save($review);
        $this->events->publish($review->pullDomainEvents());
    }
}
