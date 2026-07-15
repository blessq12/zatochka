<?php

namespace App\Domain\Order\VO;

enum OrderUrgency: string
{
    case Normal = 'normal';
    case Urgent = 'urgent';
}
