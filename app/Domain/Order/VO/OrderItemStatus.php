<?php

namespace App\Domain\Order\VO;

enum OrderItemStatus: string
{
    case Accepted = 'accepted';
    case InProduction = 'in_production';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Issued = 'issued';

    public function label(): string
    {
        return match ($this) {
            self::Accepted => 'Принят',
            self::InProduction => 'В производстве',
            self::Completed => 'Готов',
            self::Rejected => 'Отклонён',
            self::Issued => 'Выдан',
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
