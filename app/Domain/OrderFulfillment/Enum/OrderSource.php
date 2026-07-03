<?php

namespace App\Domain\OrderFulfillment\Enum;

enum OrderSource: string
{
    case Manual = 'manual';
    case SiteLead = 'site_lead';
}
