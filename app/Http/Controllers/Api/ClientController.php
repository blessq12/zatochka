<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function clientOrdersGet(Request $request)
    {
        $client = auth('sanctum')->user();

        // Получаем параметры пагинации
        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 10), 50); // Максимум 50 заказов за раз

        // Получаем заказы с пагинацией
        $ordersQuery = $client->orders()
            ->select([
                'id',
                'order_number',
                'type',
                'status',
                'urgency',
                'estimated_price',
                'actual_price',
                'problem_description',
                'created_at',
                'updated_at'
            ])
            ->orderBy('created_at', 'desc');

        // Применяем пагинацию
        $orders = $ordersQuery->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'orders' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'has_more_pages' => $orders->hasMorePages(),
            ],
        ]);
    }

    public function clientSelf()
    {
        $client = auth('sanctum')->user();

        return response()->json([
            'client' => $client,
        ]);
    }

    public function clientUpdate(Request $request)
    {
        try {
            $client = auth('sanctum')->user();

            $client->update($request->all());

            return response()->json([
                'client' => $client->fresh(), // Получаем обновленные данные
                'message' => 'Client updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

}
