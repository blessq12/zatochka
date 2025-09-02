<?php

declare(strict_types=1);

namespace App\Application\Bonuses;

use App\Domain\Bonuses\BonusTransaction;
use App\Domain\Bonuses\BonusTransactionType;
use App\Domain\Bonuses\Contracts\BonusAccountRepository;
use App\Domain\Bonuses\Contracts\BonusTransactionRepository;
use App\Domain\Bonuses\Contracts\SettingsProvider;
use App\Domain\Bonuses\Services\BonusCalculator;
use App\Models\Order;

final class AccrueBonusForOrder
{
    public function __construct(
        private readonly BonusAccountRepository $accounts,
        private readonly BonusTransactionRepository $transactions,
        private readonly SettingsProvider $settings,
        private readonly BonusCalculator $calculator,
    ) {}

    public function handle(int $orderId): void
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== $this->settings->getAccrualTriggerStatus()) {
            return;
        }

        $idempotencyKey = 'accrue:' . $order->id . ':' . $order->status;
        if ($this->transactions->existsByIdempotencyKey($idempotencyKey)) {
            return; // idempotent: already accrued for this order+status
        }

        $rule = $this->settings->getAccrualRule();
        $amount = $this->calculator->calculate($rule, (float) $order->total_price);

        $account = $this->accounts->findByClientId($order->client_id);
        if ($account === null) {
            $account = \App\Domain\Bonuses\BonusAccount::createForClient($order->client_id);
            $this->accounts->save($account);
        }

        $account->accrue($amount);
        $this->accounts->save($account);

        $tx = BonusTransaction::create(
            accountId: $account->getId(),
            type: BonusTransactionType::ACCRUE,
            amount: $amount,
            orderId: $order->id,
            relatedTransactionId: null,
            idempotencyKey: $idempotencyKey
        );
        $this->transactions->save($tx);
    }
}
