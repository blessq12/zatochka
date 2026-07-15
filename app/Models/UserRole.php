<?php

namespace App\Models;

enum UserRole: string
{
    case Manager = 'manager';
    case Master = 'master';
}
