<?php

namespace App\Domain\Pricing\VO;

enum DiscountType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
}
