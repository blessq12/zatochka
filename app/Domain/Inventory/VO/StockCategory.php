<?php

namespace App\Domain\Inventory\VO;

enum StockCategory: string
{
    case Consumable = 'consumable';
    case SparePart = 'spare_part';
}
