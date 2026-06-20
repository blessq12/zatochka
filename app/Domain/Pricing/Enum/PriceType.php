<?php

namespace App\Domain\Pricing\Enum;

enum PriceType: string
{
    case Sharpening = 'sharpening';
    case Repair = 'repair';
}
