<?php

namespace App\Domain\ClientPortal\Entities;

use App\Domain\ClientPortal\Enums\ReviewStatus;

final class Review
{
    public function __construct(
        private ?int $id,
        private int $orderId,
        private int $clientId,
        private int $rating,
        private ?string $comment,
        private ReviewStatus $status,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function clientId(): int
    {
        return $this->clientId;
    }

    public function rating(): int
    {
        return $this->rating;
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function status(): ReviewStatus
    {
        return $this->status;
    }
}
