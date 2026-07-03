<?php

namespace App\Domain\ClientPortal\Enum;

enum ReviewStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
