<?php

namespace App\Domain\Warehouse\Enum;

enum WarehouseItemType: string
{
    case Consumable = 'consumable';
    case SparePart = 'spare_part';

    public function label(): string
    {
        return match ($this) {
            self::Consumable => 'Расходник',
            self::SparePart => 'Запчасть',
        };
    }
}
