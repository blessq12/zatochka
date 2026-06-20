<?php

namespace App\Domain\Pricing\Enum;

enum PricePrefix: string
{
    case From = 'from';
    case To = 'to';

    public function label(): string
    {
        return match ($this) {
            self::From => 'от',
            self::To => 'до',
        };
    }
}
