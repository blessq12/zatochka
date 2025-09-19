<?php

namespace App\Domain\Communication\Entity;

class TelegramChat
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $clientId,
        private readonly string $username,
        private readonly int $chatId,
        private readonly bool $isActive,
        private readonly array $metadata,
        private readonly bool $isDeleted,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function withClientId(int $clientId): self
    {
        return new self(
            $this->id,
            $clientId,
            $this->username,
            $this->chatId,
            $this->isActive,
            $this->metadata,
            $this->isDeleted
        );
    }
}
