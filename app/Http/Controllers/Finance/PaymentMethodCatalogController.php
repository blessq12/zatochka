<?php

namespace App\Http\Controllers\Finance;

use App\Domain\Finance\VO\PaymentMethod;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Каталог способов оплаты — SoT = Domain\Finance\VO\PaymentMethod.
 */
final class PaymentMethodCatalogController extends Controller
{
    public function index(): JsonResponse
    {
        $items = [];

        foreach (PaymentMethod::cases() as $method) {
            $items[] = [
                'value' => $method->value,
                'label' => $method->label(),
            ];
        }

        return $this->ok($items);
    }
}
