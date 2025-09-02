<?php

declare(strict_types=1);

namespace App\Domain\Bonuses\Contracts;

use App\Domain\Bonuses\AccrualRule;
use App\Domain\Bonuses\ExpirationPolicy;
use App\Domain\Bonuses\RedemptionLimit;
use App\Domain\Bonuses\RedemptionPreference;

interface SettingsProvider
{
    public function getAccrualRule(): AccrualRule;

    public function getExpirationPolicy(): ExpirationPolicy;

    public function getRedemptionLimit(): RedemptionLimit;

    public function getDefaultRedemptionPreference(): RedemptionPreference;

    /**
     * Status name that triggers accrual (e.g., paid/completed), as defined in settings.
     */
    public function getAccrualTriggerStatus(): string;
}
