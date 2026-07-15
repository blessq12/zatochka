<?php

namespace App\Domain\Workshop\VO;

enum ProductionStatus: string
{
    case Queued = 'queued';
    case MasterAssigned = 'master_assigned';
    case Diagnosed = 'diagnosed';
    case Rejected = 'rejected';
    case InWork = 'in_work';
    case WorkCompleted = 'work_completed';
    case Completed = 'completed';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Queued => in_array($next, [self::MasterAssigned, self::Rejected], true),
            self::MasterAssigned => in_array($next, [self::Diagnosed, self::Rejected], true),
            self::Diagnosed => in_array($next, [self::InWork, self::Rejected], true),
            self::InWork => in_array($next, [self::WorkCompleted, self::Rejected], true),
            self::WorkCompleted => $next === self::Completed,
            self::Rejected, self::Completed => false,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Rejected, self::Completed], true);
    }
}
