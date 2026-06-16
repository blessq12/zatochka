<?php

namespace App\Domain\OrderFulfillment\Enum;

enum PosOrderListTab: string
{
    case New = 'new';
    case Active = 'active';
    case WaitingParts = 'waiting_parts';
    case Completed = 'completed';

    public function orderStatus(): ?OrderStatus
    {
        return match ($this) {
            self::New => OrderStatus::New,
            self::Active => OrderStatus::InWork,
            self::WaitingParts => OrderStatus::WaitingParts,
            self::Completed => OrderStatus::Ready,
        };
    }
}
