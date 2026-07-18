<?php

namespace App\Domain\Order\VO;

enum OrderStatus: string
{
    case Created = 'created';
    case MasterAssigned = 'master_assigned';
    case ReceptionCompleted = 'reception_completed';
    case InProgress = 'in_progress';
    case WorksCompleted = 'works_completed';
    case Ready = 'ready';
    case Cancelled = 'cancelled';
    case Closed = 'closed';
    case Issued = 'issued';

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

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Created => in_array($next, [self::MasterAssigned, self::ReceptionCompleted, self::Cancelled], true),
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

    /** @return array<string, string> value => label */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(
            static fn (self $case): string => $case->value,
            self::cases(),
        );
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
