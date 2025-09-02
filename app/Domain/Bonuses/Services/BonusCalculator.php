<?php

declare(strict_types=1);

namespace App\Domain\Bonuses\Services;

use App\Domain\Bonuses\AccrualRule;
use App\Domain\Bonuses\BonusAmount;

final class BonusCalculator
{
    public function calculate(AccrualRule $rule, float $orderTotal): BonusAmount
    {
        return $rule->calculateForOrderTotal($orderTotal);
    }
}
