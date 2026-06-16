<?php

namespace App\Domain\Warehouse\Enums;

enum StockMovementType: string
{
    case Received = 'received';
    case WrittenOff = 'written_off';
}
