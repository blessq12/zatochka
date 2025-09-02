<?php

declare(strict_types=1);

namespace App\Domain\Bonuses\Services;

use App\Domain\Bonuses\BonusAmount;
use App\Domain\Bonuses\RedemptionLimit;

final class BonusRedemptionPolicy
{
    public function allowedRedemption(BonusAmount $balance, RedemptionLimit $limit, float $orderTotal, ?BonusAmount $requested = null): BonusAmount
    {
        $maxByLimit = $limit->maxAllowedForOrderTotal($orderTotal);

        $cap = $maxByLimit ?? $balance;

        if ($cap instanceof BonusAmount) {
            $capValue = min($balance->toInt(), $cap->toInt());
            $cap = BonusAmount::fromInt($capValue);
        } else {
            $cap = $balance;
        }

        if ($requested === null) {
            return $cap;
        }

        $requestedValue = min($requested->toInt(), $cap->toInt());
        return BonusAmount::fromInt($requestedValue);
    }
}
