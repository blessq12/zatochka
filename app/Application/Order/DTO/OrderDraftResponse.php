<?php

namespace App\Application\Order\DTO;

final readonly class OrderDraftResponse
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public int $id,
        public string $source,
        public string $status,
        public ?int $clientId,
        public ?string $fullName,
        public ?string $phone,
        public string $serviceType,
        public array $payload,
        public bool $needsDelivery,
        public ?string $deliveryAddress,
        public ?string $comment,
        public ?int $orderId,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'source' => $this->source,
            'status' => $this->status,
            'client_id' => $this->clientId,
            'full_name' => $this->fullName,
            'phone' => $this->phone,
            'service_type' => $this->serviceType,
            'payload' => $this->payload,
            'needs_delivery' => $this->needsDelivery,
            'delivery_address' => $this->deliveryAddress,
            'comment' => $this->comment,
            'order_id' => $this->orderId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
