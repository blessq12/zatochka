<?php

namespace App\Domain\Inventory\VO;

use App\Shared\Domain\DomainException;

final readonly class StockSku
{
    private const PATTERN = '/^(CON|SPR)-\d{5}$/';

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));

        if ($normalized === '' || preg_match(self::PATTERN, $normalized) !== 1) {
            throw new DomainException('Stock SKU must match format CON-00001 or SPR-00001.');
        }

        $this->value = $normalized;
    }

    public static function generate(StockCategory $category, int $sequence): self
    {
        if ($sequence < 1) {
            throw new DomainException('Stock SKU sequence must be a positive integer.');
        }

        if ($sequence > 99999) {
            throw new DomainException('Stock SKU sequence exceeds the allowed range.');
        }

        return new self(sprintf('%s-%05d', self::prefixFor($category), $sequence));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function prefixFor(StockCategory $category): string
    {
        return match ($category) {
            StockCategory::Consumable => 'CON',
            StockCategory::SparePart => 'SPR',
        };
    }
}
