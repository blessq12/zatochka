<?php

namespace App\Domain\Warehouse\Enum;

enum StockMovementType: string
{
    case Received = 'received';
    case WrittenOff = 'written_off';
}
