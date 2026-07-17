<?php

namespace App\Domain\Identity\VO;

enum StaffRole: string
{
    case Manager = 'manager';
    case Master = 'master';
}
