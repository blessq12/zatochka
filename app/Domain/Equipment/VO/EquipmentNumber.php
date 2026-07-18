<?php

namespace App\Domain\Equipment\VO;

use App\Shared\Domain\DomainException;

final readonly class EquipmentNumber
{
    private const PATTERN = '/^EQP-\d+$/';

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));

        if ($normalized === '' || preg_match(self::PATTERN, $normalized) !== 1) {
            throw new DomainException('Equipment number must match format EQP-N (e.g. EQP-128).');
        }

        $this->value = $normalized;
    }

    public static function fromSequence(int $sequence): self
    {
        if ($sequence < 1) {
            throw new DomainException('Equipment number sequence must be a positive integer.');
        }

        return new self(sprintf('EQP-%d', $sequence));
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
