<?php

namespace App\Application\Warehouse\Presenter;

use App\Domain\Warehouse\Entity\WarehouseItem;

final class WarehouseItemPresenter
{
    /** @return array<string, mixed> */
    public static function present(WarehouseItem $item): array
    {
        return [
            'id' => $item->id(),
            'name' => $item->name(),
            'sku' => $item->sku(),
            'type' => $item->type()->value,
            'type_label' => $item->type()->label(),
            'quantity' => $item->quantity(),
            'unit' => $item->unit(),
            'price' => $item->price(),
        ];
    }

    /** @param list<WarehouseItem> $items */
    public static function list(array $items): array
    {
        return array_map(self::present(...), $items);
    }
}
