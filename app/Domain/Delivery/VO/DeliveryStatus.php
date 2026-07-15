<?php

namespace App\Domain\Delivery\VO;

enum DeliveryStatus: string
{
    case Requested = 'requested';
    case CourierAssigned = 'courier_assigned';
    case Collected = 'collected';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Requested => in_array($next, [self::CourierAssigned, self::Cancelled], true),
            self::CourierAssigned => in_array($next, [self::Collected, self::Cancelled], true),
            self::Collected => in_array($next, [self::Delivered, self::Cancelled], true),
            self::Delivered, self::Cancelled => false,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled], true);
    }
}
