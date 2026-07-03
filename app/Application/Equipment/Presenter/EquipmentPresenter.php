<?php

namespace App\Application\Equipment\Presenter;

use App\Domain\Equipment\Entity\Equipment;

final class EquipmentPresenter
{
    /** @return array<string, mixed> */
    public static function present(Equipment $equipment): array
    {
        return [
            'id' => $equipment->id(),
            'name' => $equipment->name(),
            'brand' => $equipment->brand(),
            'model' => $equipment->model(),
            'serial_numbers' => $equipment->serialNumbers(),
        ];
    }

    /** @param list<Equipment> $items */
    public static function list(array $items): array
    {
        return array_map(self::present(...), $items);
    }
}
