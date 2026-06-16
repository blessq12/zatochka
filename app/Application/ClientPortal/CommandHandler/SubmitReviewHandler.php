<?php

namespace App\Application\ClientPortal\CommandHandler;

use App\Application\ClientPortal\Command\SubmitReviewCommand;
use App\Application\ClientPortal\Support\ClientLoader;
use App\Domain\ClientPortal\Entity\Review;
use App\Domain\ClientPortal\Event\ReviewSubmitted;
use App\Domain\ClientPortal\Exception\ReviewPolicyViolation;
use App\Domain\ClientPortal\Repository\ReviewRepositoryInterface;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Exception\OrderNotFoundException;
use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;

final class SubmitReviewHandler
{
    public function __construct(
        private ClientLoader $clientLoader,
        private OrderRepositoryInterface $orders,
        private ReviewRepositoryInterface $reviews,
    ) {}

    public function handle(SubmitReviewCommand $command): Review
    {
        $this->clientLoader->load($command->clientId);

        $order = $this->orders->findByIdForClient($command->orderId, $command->clientId);

        if ($order === null) {
            throw OrderNotFoundException::withId($command->orderId);
        }

        if ($order->status() !== OrderStatus::Issued) {
            throw new ReviewPolicyViolation('Отзыв можно оставить только после выдачи заказа.');
        }

        if ($this->reviews->findByOrderId($command->orderId) !== null) {
            throw new ReviewPolicyViolation('Отзыв на этот заказ уже существует.');
        }

        $review = Review::submit(
            orderId: $command->orderId,
            clientId: $command->clientId,
            rating: $command->rating,
            comment: $command->comment,
        );

        $saved = $this->reviews->save($review);

        event(new ReviewSubmitted($saved));

        return $saved;
    }
}
