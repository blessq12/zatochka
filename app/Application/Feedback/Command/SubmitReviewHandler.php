<?php

namespace App\Application\Feedback\Command;

use App\Application\Feedback\Port\CompletedOrderPort;
use App\Application\Shared\DomainEventPublisher;
use App\Domain\Feedback\Entity\Review;
use App\Domain\Feedback\Repository\ReviewRepository;
use App\Domain\Feedback\VO\Rating;
use App\Shared\Domain\DomainException;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;

final readonly class SubmitReviewHandler
{
    public function __construct(
        private ReviewRepository $reviews,
        private CompletedOrderPort $completedOrders,
        private DomainEventPublisher $events,
    ) {}

    public function handle(SubmitReviewCommand $command): void
    {
        if (! $this->completedOrders->isCompletedForClient($command->orderId, $command->clientId)) {
            throw new DomainException('Review can be submitted only for a completed order owned by the client.');
        }

        if ($this->reviews->findByOrderId(new OrderId($command->orderId)) !== null) {
            throw new DomainException('Review for this order already exists.');
        }

        $review = Review::submit(
            new EntityId($command->reviewId),
            new OrderId($command->orderId),
            new EntityId($command->clientId),
            new Rating($command->rating),
            $command->comment,
        );

        $this->reviews->save($review);
        $this->events->publish($review->pullDomainEvents());
    }
}
