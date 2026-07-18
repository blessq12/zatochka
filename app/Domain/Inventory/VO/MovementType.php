<?php

namespace App\Domain\Inventory\VO;

enum MovementType: string
{
    case Receipt = 'receipt';
    case WriteOff = 'write_off';
    case Adjustment = 'adjustment';
    case Reversal = 'reversal';
}
