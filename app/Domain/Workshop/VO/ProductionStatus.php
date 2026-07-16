<?php

namespace App\Domain\Workshop\VO;

enum ProductionStatus: string
{
    case Queued = 'queued';
    case MasterAssigned = 'master_assigned';
    case Diagnosed = 'diagnosed';
    case Rejected = 'rejected';
    case InWork = 'in_work';
    case WaitingParts = 'waiting_parts';
    case WorkCompleted = 'work_completed';
    case Completed = 'completed';

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Queued => in_array($next, [self::MasterAssigned, self::Rejected], true),
            self::MasterAssigned => in_array($next, [self::Diagnosed, self::InWork, self::Rejected], true),
            self::Diagnosed => in_array($next, [self::InWork, self::Rejected], true),
            self::InWork => in_array($next, [self::WaitingParts, self::WorkCompleted, self::Rejected], true),
            self::WaitingParts => in_array($next, [self::InWork, self::Rejected], true),
            self::WorkCompleted => in_array($next, [self::Completed, self::Rejected, self::InWork], true),
            self::Completed => in_array($next, [self::Rejected, self::InWork], true),
            self::Rejected => false,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Rejected, self::Completed], true);
    }

    /** @return list<self> */
    public static function forFunnel(string $funnel): array
    {
        return match ($funnel) {
            'new' => [self::MasterAssigned],
            'active' => [self::Diagnosed, self::InWork],
            'waiting_parts' => [self::WaitingParts],
            'completed' => [self::WorkCompleted, self::Completed],
            default => [],
        };
    }
}
