<?php

namespace App\Domain\Equipment\VO;

enum EquipmentType: string
{
    case Clipper = 'clipper';
    case Trimmer = 'trimmer';
    case Shaver = 'shaver';
    case Dryer = 'dryer';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Clipper => 'Машинка для стрижки',
            self::Trimmer => 'Триммер',
            self::Shaver => 'Бритва',
            self::Dryer => 'Фен',
            self::Other => 'Другое',
        };
    }

    /** @return array<string, string> value => label */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(
            static fn (self $case): string => $case->value,
            self::cases(),
        );
    }

    public static function tryLabel(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value)?->label();
    }
}
