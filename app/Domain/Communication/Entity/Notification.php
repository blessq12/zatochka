<?php

namespace App\Domain\Communication\Entity;

class Notification
{
    public function __construct(
        public readonly int $id,
        public readonly int $clientId,
        public readonly string $title,
        public readonly string $message,
        public readonly string $channel,
        public readonly string $recipient,
        public readonly bool $isRead,
        public readonly ?\DateTime $sentAt,
        public readonly array $metadata
    ) {}

    public function markAsRead(): self
    {
        return new self(
            $this->id,
            $this->clientId,
            $this->title,
            $this->message,
            $this->channel,
            $this->recipient,
            true, // isRead
            $this->sentAt,
            $this->metadata
        );
    }

    public function markAsSent(): self
    {
        return new self(
            $this->id,
            $this->clientId,
            $this->title,
            $this->message,
            $this->channel,
            $this->recipient,
            $this->isRead,
            now(), // sentAt
            $this->metadata
        );
    }

    public function isSent(): bool
    {
        return $this->sentAt !== null;
    }
}
