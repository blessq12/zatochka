<?php

declare(strict_types=1);

namespace App\Application\Bonuses;

use App\Domain\Bonuses\BonusAmount;
use App\Domain\Bonuses\BonusTransaction;
use App\Domain\Bonuses\BonusTransactionType;
use App\Domain\Bonuses\Contracts\BonusAccountRepository;
use App\Domain\Bonuses\Contracts\BonusTransactionRepository;
use App\Domain\Bonuses\Contracts\SettingsProvider;
use App\Domain\Bonuses\RedemptionPreference;
use App\Domain\Bonuses\Services\BonusRedemptionCoordinator;
use App\Models\Order;

final class RequestRedemptionForOrder
{
    public function __construct(
        private readonly BonusAccountRepository $accounts,
        private readonly BonusTransactionRepository $transactions,
        private readonly SettingsProvider $settings,
        private readonly BonusRedemptionCoordinator $coordinator,
    ) {
    }

    /**
     * @param int $orderId
     * @param int|null $requestedAmount Optional explicit amount to redeem (ceil is applied in VO)
     * @param string|null $preference Override redemption preference (auto|save|manual)
     */
    public function handle(int $orderId, ?int $requestedAmount = null, ?string $preference = null): int
    {
        $order = Order::findOrFail($orderId);

        $account = $this->accounts->findByClientId($order->client_id);
        if ($account === null) {
            return 0; // nothing to redeem
        }

        $pref = $preference
            ? RedemptionPreference::fromString($preference)
            : $this->settings->getDefaultRedemptionPreference();

        $requested = $requestedAmount !== null ? BonusAmount::fromInt($requestedAmount) : null;

        $limit = $this->settings->getRedemptionLimit();

        $amount = $this->coordinator->decideAndCalculate(
            $pref,
            $account,
            $limit,
            (float) $order->total_price,
            $requested
        );

        if ($amount->isZero()) {
            return 0;
        }

        // Priority rule: ensure we don't double-apply if already redeemed for this order
        $idempotencyKey = 'redeem:' . $order->id;
        if ($this->transactions->existsByIdempotencyKey($idempotencyKey)) {
            return 0;
        }

        $account->redeem($amount);
        $this->accounts->save($account);

        $tx = BonusTransaction::create(
            accountId: $account->getId(),
            type: BonusTransactionType::REDEEM,
            amount: $amount,
            orderId: $order->id,
            relatedTransactionId: null,
            idempotencyKey: $idempotencyKey
        );
        $this->transactions->save($tx);

        return $amount->toInt();
    }
}
