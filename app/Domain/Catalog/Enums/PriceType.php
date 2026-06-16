<?php

namespace App\Domain\Catalog\Enums;

enum PriceType: string
{
    case Sharpening = 'sharpening';
    case Repair = 'repair';
}
