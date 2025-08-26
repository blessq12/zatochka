<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\BonusService;
use App\Models\Client;
use App\Models\Order;
use App\Models\BonusTransaction;

class BonusController extends Controller
{
    protected BonusService $bonusService;

    public function __construct(BonusService $bonusService)
    {
        $this->bonusService = $bonusService;
    }

    // GET /api/client/bonus/balance
    public function balance(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();
        $clientBonus = $this->bonusService->getOrCreateClientBonus($client);

        $data = [
            'balance' => (float) $clientBonus->balance,
            'total_earned' => (float) $clientBonus->total_earned,
            'total_spent' => (float) $clientBonus->total_spent,
            'expires_at' => optional($clientBonus->expires_at)->toDateTimeString(),
            'is_expired' => $clientBonus->isExpired(),
        ];

        // Простое кэширование на 10 секунд, чтобы снять пиковую нагрузку
        // (в реальном мире завязать на Cache ключ по client_id и invalidate при изменениях)

        return response()->json([
            'success' => true,
            'data' => $data,
        ])->setSharedMaxAge(10)->setMaxAge(10);
    }

    // GET /api/client/bonus/transactions
    public function transactions(Request $request): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();

        $transactions = BonusTransaction::where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ])->setSharedMaxAge(10)->setMaxAge(10);
    }

    // GET /api/client/bonus/calc-max?order_id=123
    public function calculateMax(Request $request): JsonResponse
    {
        $orderId = (int) $request->get('order_id');
        if (!$orderId) {
            return response()->json(['success' => false, 'message' => 'order_id is required'], 422);
        }

        /** @var Client $client */
        $client = $request->user();

        $order = Order::where('id', $orderId)->where('client_id', $client->id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $max = $this->bonusService->calculateMaxBonusSpend($order);

        return response()->json([
            'success' => true,
            'data' => [
                'max_spend' => $max,
            ],
        ])->setSharedMaxAge(10)->setMaxAge(10);
    }
}
