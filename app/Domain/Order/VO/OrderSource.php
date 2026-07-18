<?php

namespace App\Domain\Order\VO;

enum OrderSource: string
{
    case Website = 'website';
    case Admin = 'admin';
    case Api = 'api';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'Сайт',
            self::Admin => 'Админка',
            self::Api => 'API',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Website => 'info',
            self::Admin => 'primary',
            self::Api => 'gray',
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

    public static function tryColor(?string $value): string
    {
        return self::tryFrom((string) $value)?->color() ?? 'gray';
    }
}
