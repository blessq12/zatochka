<?php

namespace App\Domain\Identity\Enum;

enum UserRole: string
{
    case Master = 'master';
    case Manager = 'manager';
}
