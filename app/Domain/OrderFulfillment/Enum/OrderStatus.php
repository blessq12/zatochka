<?php

namespace App\Domain\OrderFulfillment\Enum;

enum OrderStatus: string
{
    case New = 'new';
    case InWork = 'in_work';
    case WaitingParts = 'waiting_parts';
    case Ready = 'ready';
    case Issued = 'issued';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новый',
            self::InWork => 'В работе',
            self::WaitingParts => 'Ожидание запчастей',
            self::Ready => 'Готов',
            self::Issued => 'Выдан',
            self::Cancelled => 'Отменён',
        };
    }
}
