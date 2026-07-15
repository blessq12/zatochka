<?php

namespace App\Domain\Order\VO;

enum OrderBillingType: string
{
    case Paid = 'paid';
    case Warranty = 'warranty';
}
