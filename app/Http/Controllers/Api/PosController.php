<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PosController extends Controller
{
    /**
     * Авторизация мастера через токены
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Создаем токен через Sanctum с уникальным именем для мастера
        $token = $user->createToken('pos_master_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Выход мастера (удаление токена)
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            // Удаляем текущий токен
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Получить информацию о текущем мастере по токену
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Получить список заказов для мастера
     */
    public function orders(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $status = $request->get('status'); // new, active, completed

        $query = Order::with(['client', 'branch'])
            ->where('is_deleted', false);

        // Фильтр по статусу
        if ($status === 'new') {
            $query->whereIn('status', [
                Order::STATUS_NEW,
                Order::STATUS_CONSULTATION,
                Order::STATUS_DIAGNOSTIC,
            ]);
        } elseif ($status === 'active') {
            $query->whereIn('status', [
                Order::STATUS_IN_WORK,
                Order::STATUS_WAITING_PARTS,
            ]);
        } elseif ($status === 'completed') {
            $query->whereIn('status', [
                Order::STATUS_READY,
                Order::STATUS_ISSUED,
                Order::STATUS_CANCELLED,
            ]);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    /**
     * Получить товары склада
     */
    public function warehouseItems(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $type = $request->get('type'); // parts, materials

        // TODO: Реализовать получение товаров склада
        // Пока возвращаем заглушку
        return response()->json([
            'items' => [],
        ]);
    }
}
