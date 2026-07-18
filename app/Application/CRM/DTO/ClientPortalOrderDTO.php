<?php

namespace App\Application\CRM\DTO;

final readonly class ClientPortalOrderDTO
{
    /**
     * @param list<string> $service_types
     * @param list<ClientPortalOrderItemDTO> $items
     */
    public function __construct(
        public string $id,
        public string $order_number,
        public array $service_types,
        public string $status,
        public string $billing_type,
        public string $urgency,
        public bool $delivery_required,
        public string $created_at,
        public ?float $price,
        public ?string $client_comment,
        public ?string $description,
        public array $items = [],
        public bool $review_exists = false,
        public ?string $review_status = null,
        public ?ClientPortalReviewDTO $review = null,
    ) {}
}
