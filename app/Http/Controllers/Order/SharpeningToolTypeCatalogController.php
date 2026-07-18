<?php

namespace App\Http\Controllers\Order;

use App\Domain\Order\VO\SharpeningToolType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Каталог типов инструмента заточки — SoT = Domain\Order\VO\SharpeningToolType.
 */
final class SharpeningToolTypeCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $items = [];

        foreach (SharpeningToolType::cases() as $type) {
            $items[] = [
                'value' => $type->value,
                'label' => $type->label(),
            ];
        }

        return $this->ok($items);
    }
}
