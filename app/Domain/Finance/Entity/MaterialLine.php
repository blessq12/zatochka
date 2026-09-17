<?php

namespace App\Domain\Finance\Entity;

use App\Shared\Domain\DomainException;

final class MaterialLine
{
    public function __construct(
        private ?int $id,
        private int $stockItemId,
        private string $amount,
    ) {
        if ($stockItemId < 1) {
            throw new DomainException('stock_item_id is required.');
        }
        self::assertAmount($amount);
    }

    public static function create(int $stockItemId, string $amount): self
    {
        return new self(null, $stockItemId, self::normalizeAmount($amount));
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function stockItemId(): int
    {
        return $this->stockItemId;
    }

    public function amount(): string
    {
        return $this->amount;
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
