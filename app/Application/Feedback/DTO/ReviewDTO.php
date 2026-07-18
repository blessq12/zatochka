<?php

namespace App\Application\Feedback\DTO;

final readonly class ReviewDTO
{
    public function __construct(
        public int $id,
        public string $orderId,
        public int $clientId,
        public int $rating,
        public ?string $comment,
        public ?string $managerReply,
        public string $status,
        public ?int $moderatedBy,
        public string $submittedAt,
        public ?string $moderatedAt,
        public ?string $hiddenAt,
        public ?string $deletedAt,
    ) {}
}
