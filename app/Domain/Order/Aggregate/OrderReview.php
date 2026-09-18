<?php

namespace App\Domain\Order\Aggregate;

use App\Shared\Domain\DomainException;

final class OrderReview
{
    public function __construct(
        private ?int $id,
        private int $orderId,
        private int $clientId,
        private int $rating,
        private ?string $text,
    ) {
        $this->assertRating($rating);
    }

    public static function create(
        int $orderId,
        int $clientId,
        int $rating,
        ?string $text = null,
    ): self {
        return new self(null, $orderId, $clientId, $rating, $text);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
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

    public function text(): ?string
    {
        return $this->text;
    }

    private function assertRating(int $rating): void
    {
        if ($rating < 1 || $rating > 5) {
            throw new DomainException('Rating must be between 1 and 5.');
        }
    }
}
