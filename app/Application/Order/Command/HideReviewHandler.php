<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Repository\ReviewRepository;
use App\Shared\ValueObject\EntityId;

final readonly class HideReviewHandler
{
    public function __construct(
        private ReviewRepository $reviews,
        private DomainEventPublisher $events,
    ) {}

    public function handle(HideReviewCommand $command): void
    {
        $review = $this->reviews->getById(new EntityId($command->reviewId));
        $review->hide();
        $this->reviews->save($review);
        $this->events->publish($review->pullDomainEvents());
    }
}
