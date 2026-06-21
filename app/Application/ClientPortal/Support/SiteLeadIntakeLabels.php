<?php

namespace App\Application\ClientPortal\Support;

final class SiteLeadIntakeLabels
{
    /** @var array<string, string> */
    public const TOOL_TYPES = [
        'manicure' => 'Маникюрные',
        'hair' => 'Парикмахерские',
        'grooming' => 'Грумерские',
        'groomer' => 'Грумерские',
        'barber' => 'Барберские',
        'other' => 'Другие',
    ];

    /** @var array<string, string> */
    public const EQUIPMENT_TYPES = [
        'clipper' => 'Машинка для стрижки',
        'trimmer' => 'Триммер',
        'shaver' => 'Бритва',
        'dryer' => 'Фен',
        'other' => 'Другое',
    ];

    public static function toolTypeLabel(string $code): string
    {
        return self::TOOL_TYPES[$code] ?? $code;
    }

    public static function equipmentTypeLabel(string $code): string
    {
        return self::EQUIPMENT_TYPES[$code] ?? $code;
    }
}
