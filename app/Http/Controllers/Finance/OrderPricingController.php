<?php

namespace App\Http\Controllers\Finance;

use App\Application\Finance\Command\UpsertOrderPricingHandler;
use App\Application\Finance\Query\GetOrderPricingByOrderHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderPricingController extends Controller
{
    public function __construct(
        private GetOrderPricingByOrderHandler $getByOrder,
        private UpsertOrderPricingHandler $upsert,
    ) {}

    public function byOrder(int $orderId): JsonResponse
    {
        $item = $this->getByOrder->handle($orderId);
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }

    public function upsertByOrder(Request $request, int $orderId): JsonResponse
    {
        $data = $request->validate([
            'lines' => ['required', 'array'],
            'lines.*.work_entry_id' => ['required', 'integer', 'min:1'],
            'lines.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        $item = $this->upsert->handle($orderId, $data['lines']);

        return response()->json($item->toArray());
    }
}
