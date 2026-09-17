<?php

namespace App\Application\Order\Command;

use App\Application\Order\Assembler\OrderResponseAssembler;
use App\Application\Order\DTO\OrderResponse;
use App\Domain\Order\Aggregate\OrderReview;
use App\Domain\Order\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\Repository\OrderReviewRepository;
use App\Shared\Domain\DomainException;
use App\Shared\Domain\ForbiddenException;

final readonly class CreateOrderReviewHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderReviewRepository $reviews,
        private OrderResponseAssembler $assembler,
    ) {}

    public function handle(
        int $orderId,
        int $clientId,
        int $rating,
        ?string $text = null,
    ): OrderResponse {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw new DomainException('Order not found.');
        }

        if ($order->clientId() !== $clientId) {
            throw new ForbiddenException('Only the order owner can leave a review.');
        }

        if ($order->status() !== OrderStatus::Issued) {
            throw new DomainException('Review is allowed only when order is issued.');
        }

        if ($this->reviews->findByOrderId($orderId) !== null) {
            throw new DomainException('Review already exists for this order.');
        }

        $this->reviews->save(OrderReview::create($orderId, $clientId, $rating, $text));

        return $this->assembler->assemble($order);
    }
}
