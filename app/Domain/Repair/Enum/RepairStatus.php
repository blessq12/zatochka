<?php

namespace App\Domain\Repair\Enum;

enum RepairStatus: string
{
    case PENDING = 'pending';
    case DIAGNOSIS = 'diagnosis';
    case IN_PROGRESS = 'in_progress';
    case WAITING_PARTS = 'waiting_parts';
    case TESTING = 'testing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Ожидает',
            self::DIAGNOSIS => 'Диагностика',
            self::IN_PROGRESS => 'В работе',
            self::WAITING_PARTS => 'Ожидание запчастей',
            self::TESTING => 'Тестирование',
            self::COMPLETED => 'Завершен',
            self::CANCELLED => 'Отменен',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::PENDING => 'Ремонт ожидает начала работы',
            self::DIAGNOSIS => 'Проводится диагностика неисправности',
            self::IN_PROGRESS => 'Ремонт в процессе выполнения',
            self::WAITING_PARTS => 'Ожидание поставки необходимых запчастей',
            self::TESTING => 'Тестирование после ремонта',
            self::COMPLETED => 'Ремонт успешно завершен',
            self::CANCELLED => 'Ремонт отменен',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::DIAGNOSIS => 'blue',
            self::IN_PROGRESS => 'yellow',
            self::WAITING_PARTS => 'orange',
            self::TESTING => 'purple',
            self::COMPLETED => 'green',
            self::CANCELLED => 'red',
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::COMPLETED, self::CANCELLED => true,
            default => false,
        };
    }

    public function isActive(): bool
    {
        return match ($this) {
            self::DIAGNOSIS, self::IN_PROGRESS, self::WAITING_PARTS, self::TESTING => true,
            default => false,
        };
    }

    public function canBeStarted(): bool
    {
        return match ($this) {
            self::PENDING => true,
            default => false,
        };
    }

    public function canBeCompleted(): bool
    {
        return match ($this) {
            self::IN_PROGRESS, self::TESTING => true,
            default => false,
        };
    }

    public static function getOptions(): array
    {
        return [
            self::PENDING->value => self::PENDING->getLabel(),
            self::DIAGNOSIS->value => self::DIAGNOSIS->getLabel(),
            self::IN_PROGRESS->value => self::IN_PROGRESS->getLabel(),
            self::WAITING_PARTS->value => self::WAITING_PARTS->getLabel(),
            self::TESTING->value => self::TESTING->getLabel(),
            self::COMPLETED->value => self::COMPLETED->getLabel(),
            self::CANCELLED->value => self::CANCELLED->getLabel(),
        ];
    }

    public static function getAll(): array
    {
        return array_column(self::cases(), 'value');
    }
}
