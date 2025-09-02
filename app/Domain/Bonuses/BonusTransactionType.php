<?php

declare(strict_types=1);

namespace App\Domain\Bonuses;

final class BonusTransactionType
{
    public const ACCRUE = 'accrue';
    public const REDEEM = 'redeem';
    public const EXPIRE = 'expire';
    public const REVERT = 'revert';

    public static function assertValid(string $type): void
    {
        if (!in_array($type, [self::ACCRUE, self::REDEEM, self::EXPIRE, self::REVERT], true)) {
            throw new \InvalidArgumentException('Invalid bonus transaction type: ' . $type);
        }
    }
}
