<?php

namespace App\Application\Order\DTO;

final readonly class OrderResponse
{
    /**
     * @param  list<array{
     *     id: int|null,
     *     kind: string,
     *     title: string|null,
     *     quantity: int|null,
     *     equipment_id: int|null,
     *     problem: string|null,
     *     position: int
     * }>  $items
     * @param  array{id: int, rating: int, text: string|null}|null  $review
     */
    public function __construct(
        public int $id,
        public int $clientId,
        public ?int $masterId,
        public string $billingType,
        public string $urgency,
        public string $estimatedCost,
        public bool $needsDelivery,
        public ?string $deliveryAddress,
        public string $status,
        public array $items,
        public ?array $review,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'master_id' => $this->masterId,
            'billing_type' => $this->billingType,
            'urgency' => $this->urgency,
            'estimated_cost' => $this->estimatedCost,
            'needs_delivery' => $this->needsDelivery,
            'delivery_address' => $this->deliveryAddress,
            'status' => $this->status,
            'items' => $this->items,
            'review' => $this->review,
        ];
    }
}
