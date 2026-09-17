<?php

namespace App\Domain\Finance;

enum CashEntrySource: string
{
    case Manual = 'manual';
    case OrderIssue = 'order_issue';
}
