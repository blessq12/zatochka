<?php

namespace App\Domain\Finance;

enum EarningsGoalStatus: string
{
    case Active = 'active';
    case Cancelled = 'cancelled';
}
