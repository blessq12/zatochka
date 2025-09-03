<?php

declare(strict_types=1);

namespace App\Application\Bonuses;

use App\Domain\Bonuses\BonusAmount;
use App\Domain\Bonuses\BonusTransaction;
use App\Domain\Bonuses\BonusTransactionType;
use App\Domain\Bonuses\Contracts\BonusAccountRepository;
use App\Domain\Bonuses\Contracts\BonusTransactionRepository;
use App\Models\Order;

final class RevertBonusesForOrder
{
    public function __construct(
        private readonly BonusAccountRepository $accounts,
        private readonly BonusTransactionRepository $transactions,
    ) {
    }

    public function handle(int $orderId): void
    {
        $order = Order::findOrFail($orderId);

        $account = $this->accounts->findByClientId($order->client_id);
        if ($account === null) {
            return;
        }

        $idempotencyKey = 'revert:' . $order->id;
        if ($this->transactions->existsByIdempotencyKey($idempotencyKey)) {
            return;
        }

        // Strategy: revert redeemed first, then accrued if needed (business-dependent). Here we revert all redemptions by order.
        // For MVP, assume one redemption per order.
        $redeemKey = 'redeem:' . $order->id;
        $redeemTx = $this->transactions->findById($redeemKey); // in infra, map idempotency usable

        if ($redeemTx !== null) {
            $account->accrue($redeemTx->getAmount());
            $this->accounts->save($account);

            $tx = BonusTransaction::create(
                accountId: $account->getId(),
                type: BonusTransactionType::REVERT,
                amount: $redeemTx->getAmount(),
                orderId: $order->id,
                relatedTransactionId: $redeemTx->getId(),
                idempotencyKey: $idempotencyKey
            );
            $this->transactions->save($tx);
        }
    }
}
