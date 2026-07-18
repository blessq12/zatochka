<?php

namespace App\Http\Controllers\Equipment;

use App\Domain\Equipment\VO\EquipmentType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Каталог типов оборудования — SoT = Domain\Equipment\VO\EquipmentType.
 */
final class EquipmentTypeCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $items = [];

        foreach (EquipmentType::cases() as $type) {
            $items[] = [
                'value' => $type->value,
                'label' => $type->label(),
            ];
        }

        return $this->ok($items);
    }
}
