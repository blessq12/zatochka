<?php

namespace App\Domain\Finance\VO;

use App\Shared\Domain\DomainException;
use DateTimeInterface;

final readonly class PaymentNumber
{
    private const PATTERN = '/^PMT-\d{2}-\d+$/';

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));

        if ($normalized === '' || preg_match(self::PATTERN, $normalized) !== 1) {
            throw new DomainException('Payment number must match format PMT-YY-N (e.g. PMT-26-128).');
        }

        $this->value = $normalized;
    }

    public static function fromSequenceAndDate(int $sequence, DateTimeInterface $createdAt): self
    {
        if ($sequence < 1) {
            throw new DomainException('Payment number sequence must be a positive integer.');
        }

        return new self(sprintf('PMT-%s-%d', $createdAt->format('y'), $sequence));
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
