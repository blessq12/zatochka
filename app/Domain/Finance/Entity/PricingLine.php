<?php

namespace App\Domain\Finance\Entity;

use App\Shared\Domain\DomainException;

final class PricingLine
{
    public function __construct(
        private ?int $id,
        private int $workEntryId,
        private string $amount,
    ) {
        if ($workEntryId < 1) {
            throw new DomainException('work_entry_id is required.');
        }
        self::assertAmount($amount);
    }

    public static function create(int $workEntryId, string $amount): self
    {
        return new self(null, $workEntryId, self::normalizeAmount($amount));
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function workEntryId(): int
    {
        return $this->workEntryId;
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function changeAmount(string $amount): void
    {
        $this->amount = self::normalizeAmount($amount);
    }

    private static function assertAmount(string $amount): void
    {
        if (! is_numeric($amount) || (float) $amount < 0) {
            throw new DomainException('amount must be a non-negative number.');
        }
    }

    private static function normalizeAmount(string $amount): string
    {
        self::assertAmount($amount);

        return number_format((float) $amount, 2, '.', '');
    }
}
