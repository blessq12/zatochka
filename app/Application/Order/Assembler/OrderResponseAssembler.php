<?php

namespace App\Application\Order\Assembler;

use App\Application\Order\DTO\OrderResponse;
use App\Domain\Order\Aggregate\Order;
use App\Domain\Order\Repository\OrderReviewRepository;

final readonly class OrderResponseAssembler
{
    public function __construct(
        private OrderReviewRepository $reviews,
    ) {}

    public function assemble(Order $order): OrderResponse
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
        if ($order->id() !== null) {
            $existing = $this->reviews->findByOrderId((int) $order->id());
            if ($existing !== null) {
                $review = [
                    'id' => (int) $existing->id(),
                    'rating' => $existing->rating(),
                    'text' => $existing->text(),
                ];
            }
        }

        return new OrderResponse(
            (int) $order->id(),
            $order->clientId(),
            $order->masterId(),
            $order->billingType()->value,
            $order->urgency()->value,
            $order->estimatedCost(),
            $order->needsDelivery(),
            $order->deliveryAddress(),
            $order->status()->value,
            $items,
            $review,
        );
    }
}
