<?php

namespace App\Domain\Feedback\VO;

enum ReviewStatus: string
{
    case PendingModeration = 'pending_moderation';
    case Published = 'published';
    case Rejected = 'rejected';
    case Hidden = 'hidden';
    case Deleted = 'deleted';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::PendingModeration => in_array($next, [self::Published, self::Rejected], true),
            self::Published => in_array($next, [self::Hidden, self::Deleted], true),
            self::Hidden => in_array($next, [self::Published, self::Deleted], true),
            self::Rejected => $next === self::Deleted,
            self::Deleted => false,
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Deleted;
    }
}
