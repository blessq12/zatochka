<?php

namespace App\Domain\Order;

enum BillingType: string
{
    case Paid = 'paid';
    case Warranty = 'warranty';
}
