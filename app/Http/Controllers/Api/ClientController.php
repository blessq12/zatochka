<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function clientOrdersGet(Request $request)
    {
        $client = auth('sanctum')->user();

        // Получаем параметры пагинации
        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 10), 50); // Максимум 50 заказов за раз

        // Получаем заказы с пагинацией и флагом «есть отзыв»
        $ordersQuery = $client->orders()
            ->select([
                'id',
                'order_number',
                'service_type',
                'status',
                'urgency',
                'price',
                'problem_description',
                'created_at',
                'updated_at',
            ])
            ->withExists('review')
            ->with('review:id,order_id,rating,comment,reply,created_at')
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

    public function setPassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $client = auth('sanctum')->user();

        if (! $client->temporary_password || $client->temporary_password_used) {
            return response()->json(
                ['message' => 'Установка пароля недоступна'],
                403
            );
        }

        $client->update([
            'password' => Hash::make($request->new_password),
            'temporary_password' => null,
            'temporary_password_used' => true,
        ]);

        return response()->json([
            'message' => 'Пароль успешно установлен',
            'client' => $client->fresh(),
        ]);
    }
}
