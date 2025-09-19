<?php

namespace App\Domain\Communication\Entity;

class TelegramChat
{
    public function __construct(
        public readonly int $id,
        public readonly int $clientId,
        public readonly string $username,
        public readonly string $chatId,
        public readonly bool $isActive,
        public readonly array $metadata,
        public readonly bool $isDeleted = false
    ) {}

    public function activate(): self
    {
        return new self(
            $this->id,
            $this->clientId,
            $this->username,
            $this->chatId,
            true, // isActive
            array_merge($this->metadata, ['verified_at' => now()]),
            $this->isDeleted
        );
    }

    public function isVerified(): bool
    {
        return $this->isActive;
    }

    public function isPendingVerification(): bool
    {
        return !$this->isActive && isset($this->metadata['verification_pending']);
    }
}
