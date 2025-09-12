<?php

namespace App\Domain\Order\Enum;

enum OrderStatus: string
{
    case NEW = 'new';
    case CONSULTATION = 'consultation';
    case DIAGNOSTIC = 'diagnostic';
    case IN_WORK = 'in_work';
    case WAITING_PARTS = 'waiting_parts';
    case READY = 'ready';
    case ISSUED = 'issued';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Новый',
            self::CONSULTATION => 'Консультация',
            self::DIAGNOSTIC => 'Диагностика',
            self::IN_WORK => 'В работе',
            self::WAITING_PARTS => 'Ожидание запчастей',
            self::READY => 'Готов',
            self::ISSUED => 'Выдан',
            self::CANCELLED => 'Отменен',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::NEW => 'Новый заказ, требует обработки',
            self::CONSULTATION => 'Консультация с клиентом',
            self::DIAGNOSTIC => 'Диагностика неисправности',
            self::IN_WORK => 'Заказ в работе у мастера',
            self::WAITING_PARTS => 'Ожидание поставки запчастей',
            self::READY => 'Заказ готов к выдаче',
            self::ISSUED => 'Заказ выдан клиенту',
            self::CANCELLED => 'Заказ отменен',
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::ISSUED, self::CANCELLED => true,
            default => false,
        };
    }

    public function isManagerStatus(): bool
    {
        return match ($this) {
            self::NEW, self::CONSULTATION, self::READY, self::ISSUED, self::CANCELLED => true,
            default => false,
        };
    }

    public function isWorkshopStatus(): bool
    {
        return match ($this) {
            self::DIAGNOSTIC, self::IN_WORK, self::WAITING_PARTS => true,
            default => false,
        };
    }

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }
        return $options;
    }

    public static function getManagerStatuses(): array
    {
        return array_filter(self::cases(), fn($status) => $status->isManagerStatus());
    }

    public static function getWorkshopStatuses(): array
    {
        return array_filter(self::cases(), fn($status) => $status->isWorkshopStatus());
    }

    public static function getFinalStatuses(): array
    {
        return array_filter(self::cases(), fn($status) => $status->isFinal());
    }
}
