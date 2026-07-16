<?php

namespace App\Domain\Order\VO;

use App\Shared\Domain\DomainException;

final readonly class OrderId
{
    private const PATTERN = '/^[a-f0-9]{32}$/';

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '' || preg_match(self::PATTERN, $normalized) !== 1) {
            throw new DomainException('Order id must be a 32-character hex hash.');
        }

        $this->value = $normalized;
    }

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(16)));
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
