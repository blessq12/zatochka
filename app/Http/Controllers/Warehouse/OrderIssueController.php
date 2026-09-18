<?php

namespace App\Http\Controllers\Warehouse;

use App\Application\Warehouse\Query\GetOrderIssueByOrderHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class OrderIssueController extends Controller
{
    public function __construct(
        private GetOrderIssueByOrderHandler $getByOrder,
    ) {}

    public function byOrder(int $orderId): JsonResponse
    {
        $item = $this->getByOrder->handle($orderId);
        if ($item === null) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($item->toArray());
    }
}
