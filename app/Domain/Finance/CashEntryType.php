<?php

namespace App\Domain\Finance;

enum CashEntryType: string
{
    case Income = 'income';
    case Expense = 'expense';
}
