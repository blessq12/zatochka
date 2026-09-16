<?php

namespace App\Application\Order\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Order\Entity\Review;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\Repository\ReviewRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\Rating;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class SubmitReviewHandler
{
    public function __construct(
        private ReviewRepository $reviews,
        private OrderRepository $orders,
        private DomainEventPublisher $events,
    ) {}

    public function handle(SubmitReviewCommand $command): void
    {
        $order = $this->orders->findById(new OrderId($command->orderId));

        if (
            $order === null
            || $order->clientId()->value !== $command->clientId
            || ! in_array($order->status(), [OrderStatus::Issued, OrderStatus::Closed], true)
        ) {
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
