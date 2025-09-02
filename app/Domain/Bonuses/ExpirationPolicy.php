<?php

declare(strict_types=1);

namespace App\Domain\Bonuses;

final class ExpirationPolicy
{
    public const TYPE_FIXED_DAYS = 'fixed_days';

    private string $type;
    private int $days;

    private function __construct(string $type, int $days)
    {
        if ($type !== self::TYPE_FIXED_DAYS) {
            throw new \InvalidArgumentException('Unsupported expiration policy type: ' . $type);
        }
        if ($days <= 0) {
            throw new \InvalidArgumentException('Expiration days must be positive');
        }
        $this->type = $type;
        $this->days = $days;
    }

    public static function fixedDays(int $days): self
    {
        return new self(self::TYPE_FIXED_DAYS, $days);
    }

    public function calculateExpiryDate(\DateTimeImmutable $from): \DateTimeImmutable
    {
        return $from->modify('+' . $this->days . ' days');
    }
}
