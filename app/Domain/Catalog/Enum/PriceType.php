<?php

namespace App\Domain\Catalog\Enum;

enum PriceType: string
{
    case Sharpening = 'sharpening';
    case Repair = 'repair';
}
