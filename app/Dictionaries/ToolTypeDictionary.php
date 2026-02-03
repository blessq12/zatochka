<?php

namespace App\Dictionaries;

/**
 * Словарь типов инструментов для заточки.
 * Slug используется в БД и при передаче с фронта, label — для отображения.
 */
class ToolTypeDictionary
{
    public const MANICURE = 'manicure';

    public const HAIR = 'hair';

    public const GROOMING = 'grooming';

    public const BARBER = 'barber';

    public const OTHER = 'other';

    public static function getLabels(): array
    {
        return [
            self::MANICURE => 'Маникюрные',
            self::HAIR => 'Парикмахерские',
            self::GROOMING => 'Грумерские',
            self::BARBER => 'Барберские',
            self::OTHER => 'Другие',
        ];
    }

    public static function getLabel(?string $slug): string
    {
        if ($slug === null || $slug === '') {
            return '';
        }
        $labels = self::getLabels();
        return $labels[$slug] ?? $slug;
    }

    public static function isValid(string $slug): bool
    {
        return array_key_exists($slug, self::getLabels());
    }
}
