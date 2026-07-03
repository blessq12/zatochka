<?php

namespace App\Http\Controllers\Pos;

use App\Application\OrderFulfillment\ReadModel\PosOrderReadModelBuilder;
use App\Domain\OrderFulfillment\Entity\Order;
use Illuminate\Http\JsonResponse;

trait RendersPosOrder
{
    protected function orderResponse(Order $order, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => app(PosOrderReadModelBuilder::class)->build($order),
        ], $status);
    }

    protected function orderDetailResponse(Order $order): JsonResponse
    {
        return $this->orderResponse($order);
    }
}
