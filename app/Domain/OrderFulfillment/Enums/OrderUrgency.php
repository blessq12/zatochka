<?php

namespace App\Domain\OrderFulfillment\Enums;

enum OrderUrgency: string
{
    case Standard = 'standard';
    case Urgent = 'urgent';
}
