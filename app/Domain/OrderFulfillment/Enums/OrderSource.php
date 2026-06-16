<?php

namespace App\Domain\OrderFulfillment\Enums;

enum OrderSource: string
{
    case Manual = 'manual';
    case SiteLead = 'site_lead';
}
