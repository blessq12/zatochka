<?php

namespace App\Domain\Order\VO;

enum OrderStatus: string
{
    case Created = 'created';
    case ReceptionCompleted = 'reception_completed';
    case InProgress = 'in_progress';
    case Ready = 'ready';
    case Cancelled = 'cancelled';
    case Closed = 'closed';
    case Issued = 'issued';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Created => in_array($next, [self::ReceptionCompleted, self::Cancelled], true),
            self::ReceptionCompleted => in_array($next, [self::InProgress, self::Cancelled], true),
            self::InProgress => in_array($next, [self::Ready, self::Cancelled], true),
            self::Ready => in_array($next, [self::Issued, self::Closed, self::Cancelled], true),
            self::Cancelled, self::Closed, self::Issued => false,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Cancelled, self::Closed, self::Issued], true);
    }
}
