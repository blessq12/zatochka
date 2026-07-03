<?php

namespace App\Application\ClientPortal\Support;

use App\Infrastructure\ClientPortal\Persistence\Eloquent\SiteLeadModel;

final class SiteLeadIntakePresenter
{
    public static function summary(SiteLeadModel $lead): string
    {
        /** @var array<string, mixed> $intake */
        $intake = $lead->intake_data ?? [];

        if ($intake === []) {
            return $lead->comment !== null && $lead->comment !== ''
                ? (string) $lead->comment
                : '—';
        }

        $serviceType = self::primaryServiceType($lead->service_types ?? []);

        return match ($serviceType) {
            'repair' => self::repairSummary($intake),
            'sharpening' => self::sharpeningSummary($intake),
            default => '—',
        };
    }

    /**
     * @param  list<string>  $serviceTypes
     */
    private static function primaryServiceType(array $serviceTypes): ?string
    {
        foreach ($serviceTypes as $type) {
            if (in_array($type, ['repair', 'sharpening'], true)) {
                return (string) $type;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $intake
     */
    private static function sharpeningSummary(array $intake): string
    {
        $parts = [];

        $toolType = (string) ($intake['tool_type'] ?? '');

        if ($toolType !== '') {
            $parts[] = SiteLeadIntakeLabels::toolTypeLabel($toolType);
        }

        if (isset($intake['tools_count'])) {
            $parts[] = (int) $intake['tools_count'].' шт.';
        }

        $extra = trim((string) ($intake['extra_comment'] ?? ''));

        if ($extra !== '') {
            $parts[] = $extra;
        }

        return $parts !== [] ? implode(' · ', $parts) : '—';
    }

    /**
     * @param  array<string, mixed>  $intake
     */
    private static function repairSummary(array $intake): string
    {
        $parts = [];

        $equipmentType = (string) ($intake['equipment_type'] ?? '');

        if ($equipmentType !== '') {
            $parts[] = SiteLeadIntakeLabels::equipmentTypeLabel($equipmentType);
        }

        $deviceName = trim((string) ($intake['device_name'] ?? ''));

        if ($deviceName !== '') {
            $parts[] = $deviceName;
        }

        $problem = trim((string) ($intake['problem_description'] ?? ''));

        if ($problem !== '') {
            $parts[] = $problem;
        }

        if (($intake['urgency_type'] ?? '') === 'urgent') {
            $parts[] = 'срочно';
        }

        return $parts !== [] ? implode(' · ', $parts) : '—';
    }
}
