<?php

namespace App\Domain\Order;

enum Urgency: string
{
    case Normal = 'normal';
    case Urgent = 'urgent';
}
