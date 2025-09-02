<?php

declare(strict_types=1);

namespace App\Domain\Bonuses\Services;

use App\Domain\Bonuses\BonusAccount;
use App\Domain\Bonuses\BonusAmount;
use App\Domain\Bonuses\RedemptionLimit;
use App\Domain\Bonuses\RedemptionPreference;

final class BonusRedemptionCoordinator
{
    private BonusRedemptionPolicy $policy;

    public function __construct(BonusRedemptionPolicy $policy)
    {
        $this->policy = $policy;
    }

    public function decideAndCalculate(
        RedemptionPreference $preference,
        BonusAccount $account,
        RedemptionLimit $limit,
        float $orderTotal,
        ?BonusAmount $requested
    ): BonusAmount {
        if ($preference->isSave()) {
            return BonusAmount::fromInt(0);
        }

        if ($preference->isManual() && $requested === null) {
            return BonusAmount::fromInt(0);
        }

        return $this->policy->allowedRedemption($account->getBalance(), $limit, $orderTotal, $requested);
    }
}
