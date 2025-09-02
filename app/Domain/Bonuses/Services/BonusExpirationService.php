<?php

declare(strict_types=1);

namespace App\Domain\Bonuses\Services;

use App\Domain\Bonuses\BonusAccount;
use App\Domain\Bonuses\BonusAmount;

final class BonusExpirationService
{
    /**
     * Simplified: expire a given amount (pre-calculated externally by policy/CSR).
     */
    public function expire(BonusAccount $account, BonusAmount $amount): void
    {
        if ($amount->isZero()) {
            return;
        }
        $account->expire($amount);
    }
}
