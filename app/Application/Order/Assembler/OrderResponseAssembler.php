<?php

namespace App\Application\Order\Assembler;

use App\Application\Order\DTO\OrderResponse;
use App\Domain\Finance\Repository\OrderPricingRepository;
use App\Domain\Order\Aggregate\Order;
use App\Domain\Order\Repository\OrderCommentRepository;
use App\Domain\Order\Repository\OrderReviewRepository;

final readonly class OrderResponseAssembler
{
    public function __construct(
        private OrderReviewRepository $reviews,
        private OrderCommentRepository $comments,
        private OrderPricingRepository $pricings,
    ) {}

    public function assemble(Order $order, bool $includeComments = false): OrderResponse
    {
        $items = [];
        foreach ($order->items() as $item) {
            $items[] = [
                'id' => $item->id(),
                'kind' => $item->kind()->value,
                'title' => $item->title(),
                'quantity' => $item->quantity(),
                'equipment_id' => $item->equipmentId(),
                'problem' => $item->problem(),
                'position' => $item->position(),
            ];
        }

        $review = null;
        $comments = null;
        $actualCost = null;
        if ($order->id() !== null) {
            $orderId = (int) $order->id();
            $existing = $this->reviews->findByOrderId($orderId);
            if ($existing !== null) {
                $review = [
                    'id' => (int) $existing->id(),
                    'rating' => $existing->rating(),
                    'text' => $existing->text(),
                ];
            }

            $pricing = $this->pricings->findByOrderId($orderId);
            if ($pricing !== null) {
                $actualCost = $pricing->total();
            }

            if ($includeComments) {
                $comments = [];
                foreach ($this->comments->listByOrderId($orderId) as $comment) {
                    $comments[] = [
                        'id' => (int) $comment->id(),
                        'author_type' => $comment->authorType(),
                        'author_id' => $comment->authorId(),
                        'body' => $comment->body(),
                        'kind' => $comment->kind()->value,
                        'created_at' => $comment->createdAt()?->format(DATE_ATOM),
                    ];
                }
            }
        }

        return new OrderResponse(
            (int) $order->id(),
            $order->clientId(),
            $order->masterId(),
            $order->billingType()->value,
            $order->urgency()->value,
            $order->estimatedCost(),
            $actualCost,
            $order->needsDelivery(),
            $order->deliveryAddress(),
            $order->status()->value,
            $items,
            $review,
            $order->createdAt()?->format(DATE_ATOM),
            $order->issuedAt()?->format(DATE_ATOM),
            $comments,
        );
    }
}
