<?php

namespace App\Domain\Order\VO;

enum OrderStatus: string
{
    case Created = 'created';
    case MasterAssigned = 'master_assigned';
    case InProgress = 'in_progress';
    case WorksCompleted = 'works_completed';
    case Ready = 'ready';
    case Cancelled = 'cancelled';
    case Closed = 'closed';
    case Issued = 'issued';

    /**
     * Legacy DB value — not part of MVP FSM. Kept for reconstituting old rows until migrated.
     *
     * @deprecated Use MasterAssigned / InProgress
     */
    case ReceptionCompleted = 'reception_completed';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Создан',
            self::MasterAssigned => 'Мастер назначен',
            self::ReceptionCompleted => 'Приёмка завершена',
            self::InProgress => 'В работе',
            self::WorksCompleted => 'Работы завершены',
            self::Ready => 'Готов к выдаче',
            self::Cancelled => 'Отменён',
            self::Closed => 'Закрыт',
            self::Issued => 'Выдан',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Cancelled => 'danger',
            self::Issued, self::Closed => 'success',
            self::Ready => 'info',
            self::WorksCompleted,
            self::InProgress,
            self::MasterAssigned,
            self::ReceptionCompleted => 'warning',
            self::Created => 'gray',
        };
    }

    /**
     * MVP Order FSM:
     * created → master_assigned → in_progress → works_completed → ready → issued|closed
     * created → cancelled
     * works_completed → in_progress (return to master)
     * Legacy: reception_completed → in_progress (escape hatch only).
     */
    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Created => in_array($next, [self::MasterAssigned, self::Cancelled], true),
            self::MasterAssigned => $next === self::InProgress,
            self::ReceptionCompleted => $next === self::InProgress,
            self::InProgress => $next === self::WorksCompleted,
            self::WorksCompleted => in_array($next, [self::Ready, self::InProgress], true),
            self::Ready => in_array($next, [self::Issued, self::Closed], true),
            self::Cancelled, self::Closed, self::Issued => false,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Cancelled, self::Closed, self::Issued], true);
    }

    /** @return array<string, string> value => label (MVP statuses only) */
    public static function options(): array
    {
        $options = [];

        foreach ([
            self::Created,
            self::MasterAssigned,
            self::InProgress,
            self::WorksCompleted,
            self::Ready,
            self::Issued,
            self::Closed,
            self::Cancelled,
        ] as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_keys(self::options());
    }

    public static function tryLabel(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value)?->label();
    }

    public static function tryColor(?string $value): string
    {
        return self::tryFrom((string) $value)?->color() ?? 'gray';
    }
}
