<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Domain\Bonuses\AccrualRule;
use App\Domain\Bonuses\Contracts\SettingsProvider;
use App\Domain\Bonuses\ExpirationPolicy;
use App\Domain\Bonuses\RedemptionLimit;
use App\Domain\Bonuses\RedemptionPreference;
use App\Models\BonusSettings;

final class BonusSettingsProvider implements SettingsProvider
{
    public function getAccrualRule(): AccrualRule
    {
        $settings = BonusSettings::getSettings();
        // Prefer percent per order from settings; cap by max_bonus_per_order will be handled where needed
        return AccrualRule::percent((float) $settings->percent_per_order);
    }

    public function getExpirationPolicy(): ExpirationPolicy
    {
        $settings = BonusSettings::getSettings();
        return ExpirationPolicy::fixedDays((int) $settings->expire_days);
    }

    public function getRedemptionLimit(): RedemptionLimit
    {
        $settings = BonusSettings::getSettings();
        // Using max_bonus_per_order as hard cap independent of order total; if min_order_sum_for_spending is needed, enforce externally
        if (!empty($settings->max_bonus_per_order)) {
            // We do not have absolute cap VO; translate to percent if order total unknown at this level.
            // Fallback to no percent cap; enforcement of absolute cap should be applied at use-case layer if needed.
        }

        // If there is a business rule for percent cap, add field and map here. For now: no percent cap.
        return RedemptionLimit::none();
    }

    public function getDefaultRedemptionPreference(): RedemptionPreference
    {
        // No explicit field in settings; default to manual to prevent auto write-off by default.
        return RedemptionPreference::manual();
    }

    public function getAccrualTriggerStatus(): string
    {
        // No explicit field in settings; fallback to 'paid'. Replace when field is added.
        return 'paid';
    }
}
