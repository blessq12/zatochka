<?php

namespace App\Application\CRM\DTO;

final readonly class ClientPortalOrderItemDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $tool_type_label,
        public ?int $quantity,
        public string $status,
        public string $status_label,
    ) {}
}
