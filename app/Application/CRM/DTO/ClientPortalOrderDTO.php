<?php

namespace App\Application\CRM\DTO;

final readonly class ClientPortalOrderDTO
{
    /**
     * @param list<string> $service_types
     */
    public function __construct(
        public string $id,
        public string $order_number,
        public array $service_types,
        public string $created_at,
        public ?float $price,
        public ?string $description,
        public bool $review_exists = false,
        public ?string $review_status = null,
    ) {}
}
