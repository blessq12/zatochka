<?php

namespace App\Domain\Order\VO;

use App\Shared\Domain\DomainException;
use DateTimeInterface;

final readonly class OrderNumber
{
    private const PATTERN = '/^ORD-\d{2}-\d+$/';

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));

        if ($normalized === '' || preg_match(self::PATTERN, $normalized) !== 1) {
            throw new DomainException('Order number must match format ORD-YY-N (e.g. ORD-26-128).');
        }

        $this->value = $normalized;
    }

    public static function fromSequenceAndDate(int $sequence, DateTimeInterface $createdAt): self
    {
        if ($sequence < 1) {
            throw new DomainException('Order number sequence must be a positive integer.');
        }

        return new self(sprintf('ORD-%s-%d', $createdAt->format('y'), $sequence));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
