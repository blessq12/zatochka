<?php

namespace App\Domain\CRM\Entity;

use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class BonusAccount
{
    private string $balance;

    public function __construct(
        private readonly EntityId $id,
        string $balance = '0',
    ) {
        if (! is_numeric($balance) || (float) $balance < 0) {
            throw new DomainException('Bonus balance must be a non-negative number.');
        }

        $this->balance = number_format((float) $balance, 2, '.', '');
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function balance(): string
    {
        return $this->balance;
    }

    public function accrue(string $amount): void
    {
        if (! is_numeric($amount) || (float) $amount <= 0) {
            throw new DomainException('Bonus accrual amount must be positive.');
        }

        $this->balance = number_format((float) $this->balance + (float) $amount, 2, '.', '');
    }
}
