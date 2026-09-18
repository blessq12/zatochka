<?php

namespace App\Domain\Order;

enum OrderDraftStatus: string
{
    case Pending = 'pending';
    case Cancelled = 'cancelled';
    case Promoted = 'promoted';

    public function isPending(): bool
    {
        return $this === self::Pending;
    }
}
