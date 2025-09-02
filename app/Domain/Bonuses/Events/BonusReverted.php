<?php

declare(strict_types=1);

namespace App\Domain\Bonuses\Events;

final class BonusReverted
{
    public function __construct(
        public readonly int $accountId,
        public readonly int $clientId,
        public readonly int $amount,
        public readonly ?int $orderId,
        public readonly string $transactionId,
        public readonly string $revertedTransactionId
    ) {}
}
