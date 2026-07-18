<?php

namespace App\Application\CRM\DTO;

final readonly class ClientPortalReviewDTO
{
    public function __construct(
        public int $rating,
        public ?string $comment,
        public ?string $manager_reply,
        public string $status,
        public string $submitted_at,
    ) {}
}
