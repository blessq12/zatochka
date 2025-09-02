<?php

declare(strict_types=1);

namespace App\Domain\Bonuses\Events;

final class BonusExpired
{
    public function __construct(
        public readonly int $accountId,
        public readonly int $clientId,
        public readonly int $amount,
        public readonly string $transactionId
    ) {}
}
