<?php

declare(strict_types=1);

namespace App\Domain\Bonuses;

final class BonusAccount
{
    private int $id;
    private int $clientId;
    private BonusAmount $balance;

    private function __construct(int $id, int $clientId, BonusAmount $balance)
    {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->balance = $balance;
    }

    public static function createForClient(int $clientId): self
    {
        // id = 0 indicates not persisted yet; infrastructure assigns real id on save
        return new self(0, $clientId, BonusAmount::fromInt(0));
    }

    public static function restore(int $id, int $clientId, int $balance): self
    {
        return new self($id, $clientId, BonusAmount::fromInt($balance));
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function getClientId(): int
    {
        return $this->clientId;
    }
    public function getBalance(): BonusAmount
    {
        return $this->balance;
    }

    public function accrue(BonusAmount $amount): void
    {
        $this->balance = $this->balance->add($amount);
    }

    public function redeem(BonusAmount $amount): void
    {
        // Business rule: no negative balances
        $this->balance = $this->balance->subtract($amount);
    }

    public function expire(BonusAmount $amount): void
    {
        $this->balance = $this->balance->subtract($amount);
    }
}
