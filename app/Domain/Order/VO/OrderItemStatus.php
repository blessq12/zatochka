<?php

namespace App\Domain\Order\VO;

enum OrderItemStatus: string
{
    case Accepted = 'accepted';
    case InProduction = 'in_production';
    case Completed = 'completed';
    case Rejected = 'rejected';
    case Issued = 'issued';
}
