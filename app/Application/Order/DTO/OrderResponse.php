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
     * @param  list<array{
     *     id: int,
     *     author_type: string,
     *     author_id: int,
     *     body: string,
     *     kind: string,
     *     created_at: string|null
     * }>|null  $comments
     */
    public function __construct(
        public int $id,
        public int $clientId,
        public ?int $masterId,
        public string $billingType,
        public string $urgency,
        public string $estimatedCost,
        public ?string $actualCost,
        public bool $needsDelivery,
        public ?string $deliveryAddress,
        public string $status,
        public array $items,
        public ?array $review,
        public ?string $createdAt,
        public ?string $issuedAt,
        public ?array $comments = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'master_id' => $this->masterId,
            'billing_type' => $this->billingType,
            'urgency' => $this->urgency,
            'estimated_cost' => $this->estimatedCost,
            'actual_cost' => $this->actualCost,
            'needs_delivery' => $this->needsDelivery,
            'delivery_address' => $this->deliveryAddress,
            'status' => $this->status,
            'items' => $this->items,
            'review' => $this->review,
            'created_at' => $this->createdAt,
            'issued_at' => $this->issuedAt,
        ];

        if ($this->comments !== null) {
            $data['comments'] = $this->comments;
        }

        return $data;
    }
}
