<?php

namespace App\Domain\Finance;

enum OrderPricingStatus: string
{
    case Draft = 'draft';
    case Priced = 'priced';
}
