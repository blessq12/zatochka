<?php

namespace App\Domain\Order\VO;

enum OrderServiceType: string
{
    case Sharpening = 'sharpening';
    case Repair = 'repair';

    public function label(): string
    {
        return match ($this) {
            self::Sharpening => 'Заточка',
            self::Repair => 'Ремонт',
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
