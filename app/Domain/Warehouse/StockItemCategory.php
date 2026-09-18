<?php

namespace App\Domain\Warehouse;

enum StockItemCategory: string
{
    case SparePart = 'spare_part';
    case Consumable = 'consumable';
}
