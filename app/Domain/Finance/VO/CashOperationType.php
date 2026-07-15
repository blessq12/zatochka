<?php

namespace App\Domain\Finance\VO;

enum CashOperationType: string
{
    case In = 'in';
    case Out = 'out';
}
