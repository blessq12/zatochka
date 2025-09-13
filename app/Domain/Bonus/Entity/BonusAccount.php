<?php

namespace App\Domain\Bonus\Entity;

class BonusAccount
{
    public function __construct(
        private readonly int $id,
        private readonly int $clientId,
        private readonly int $balance,
        private readonly \DateTime $createdAt,
        private readonly \DateTime $updatedAt
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function hasSufficientBalance(int $amount): bool
    {
        return $this->balance >= $amount;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'balance' => $this->balance,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
