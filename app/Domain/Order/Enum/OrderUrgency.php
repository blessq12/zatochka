<?php

namespace App\Domain\Order\Enum;

enum OrderUrgency: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function getLabel(): string
    {
        return match($this) {
            self::LOW => 'Низкая',
            self::NORMAL => 'Обычная',
            self::HIGH => 'Высокая',
            self::URGENT => 'Срочная',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::LOW => 'gray',
            self::NORMAL => 'blue',
            self::HIGH => 'orange',
            self::URGENT => 'red',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }

    public static function getDefault(): self
    {
        return self::NORMAL;
    }
}
