<?php

namespace App\Domain\Communication\Entity;

use DateTime;

class TelegramMessage
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $chatId,
        private readonly ?int $clientId,
        private readonly string $content,
        private readonly string $direction,
        private readonly DateTime $sentAt,
        private readonly bool $isDeleted,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function getSentAt(): DateTime
    {
        return $this->sentAt;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function withClientId(int $clientId): self
    {
        return new self(
            $this->id,
            $this->chatId,
            $clientId,
            $this->content,
            $this->direction,
            $this->sentAt,
            $this->isDeleted
        );
    }
}
