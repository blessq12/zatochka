<?php

namespace App\Domain\Order;

enum OrderStatus: string
{
    case Created = 'created';
    case MasterAssigned = 'master_assigned';
    case InProgress = 'in_progress';
    case WaitingParts = 'waiting_parts';
    case WorksCompleted = 'works_completed';
    case Ready = 'ready';
    case Issued = 'issued';
    case Cancelled = 'cancelled';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Created => [self::MasterAssigned, self::Cancelled],
            self::MasterAssigned => [self::InProgress, self::Cancelled],
            self::InProgress => [self::WaitingParts, self::WorksCompleted],
            self::WaitingParts => [self::InProgress],
            self::WorksCompleted => [self::Ready, self::InProgress],
            self::Ready => [self::Issued],
            self::Issued, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function allowsItemEdit(): bool
    {
        return $this === self::Created;
    }
}
