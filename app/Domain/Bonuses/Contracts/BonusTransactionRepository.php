<?php

declare(strict_types=1);

namespace App\Domain\Bonuses\Contracts;

use App\Domain\Bonuses\BonusTransaction;

interface BonusTransactionRepository
{
    public function save(BonusTransaction $transaction): void;

    public function existsByIdempotencyKey(string $key): bool;

    /**
     * @return BonusTransaction[]
     */
    public function listByAccountId(int $accountId, int $limit = 100, int $offset = 0): array;

    public function findById(string $transactionId): ?BonusTransaction;

    public function findByOrderAndType(int $orderId, string $type): ?BonusTransaction;
}
