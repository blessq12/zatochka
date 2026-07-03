<?php

namespace App\Domain\OrderFulfillment\Enum;

enum OrderUrgency: string
{
    case Standard = 'standard';
    case Urgent = 'urgent';
}
