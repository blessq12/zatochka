<?php

declare(strict_types=1);

namespace App\Application\Bonuses;

use App\Domain\Bonuses\BonusAmount;
use App\Domain\Bonuses\BonusTransaction;
use App\Domain\Bonuses\BonusTransactionType;
use App\Domain\Bonuses\Contracts\BonusAccountRepository;
use App\Domain\Bonuses\Contracts\BonusTransactionRepository;
use App\Domain\Bonuses\Contracts\SettingsProvider;
use App\Domain\Bonuses\Services\BonusExpirationService;

final class ExpireBonuses
{
    public function __construct(
        private readonly BonusAccountRepository $accounts,
        private readonly BonusTransactionRepository $transactions,
        private readonly SettingsProvider $settings,
        private readonly BonusExpirationService $expiration,
    ) {}

    /**
     * Simplified: expire a fixed amount per account decided externally; here we assume policy returns full balance if expired.
     * In a real FIFO model we would expire per-lot; this MVP keeps it simple.
     */
    public function handle(int $accountId, int $amountToExpire): void
    {
        $account = $this->accounts->findById($accountId);
        if ($account === null || $amountToExpire <= 0) {
            return;
        }

        $amount = BonusAmount::fromInt($amountToExpire);

        $idempotencyKey = 'expire:' . $account->getId() . ':' . (new \DateTimeImmutable('today'))->format('Y-m-d') . ':' . $amount->toInt();
        if ($this->transactions->existsByIdempotencyKey($idempotencyKey)) {
            return;
        }

        $this->expiration->expire($account, $amount);
        $this->accounts->save($account);

        $tx = BonusTransaction::create(
            accountId: $account->getId(),
            type: BonusTransactionType::EXPIRE,
            amount: $amount,
            orderId: null,
            relatedTransactionId: null,
            idempotencyKey: $idempotencyKey
        );
        $this->transactions->save($tx);
    }
}
