<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $master = Master::where('email', $request->email)
            ->where('is_deleted', false)
            ->first();

        if (!$master || !Hash::check($request->password, $master->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Создаем токен через Sanctum с уникальным именем для мастера
        $token = $master->createToken('pos_master_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $master->id,
                'name' => $master->name,
                'surname' => $master->surname,
                'email' => $master->email,
                'phone' => $master->phone,
                'telegram_username' => $master->telegram_username,
                'notifications_enabled' => $master->notifications_enabled,
            ],
        ]);
    }

    /**
     * Выход мастера (удаление токена)
     */
    public function logout(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if ($master) {
            // Удаляем текущий токен
            $master->currentAccessToken()->delete();
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
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'user' => [
                'id' => $master->id,
                'name' => $master->name,
                'surname' => $master->surname,
                'email' => $master->email,
                'phone' => $master->phone,
                'telegram_username' => $master->telegram_username,
                'notifications_enabled' => $master->notifications_enabled,
            ],
        ]);
    }

    /**
     * Обновить профиль мастера
     */
    public function updateProfile(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'surname' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'telegram_username' => 'nullable|string|max:255',
            'notifications_enabled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Убираем @ из telegram_username если есть
        if (isset($data['telegram_username'])) {
            $data['telegram_username'] = ltrim($data['telegram_username'], '@');
            if (empty($data['telegram_username'])) {
                $data['telegram_username'] = null;
            }
        }

        $master->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $master->id,
                'name' => $master->name,
                'surname' => $master->surname,
                'email' => $master->email,
                'phone' => $master->phone,
                'telegram_username' => $master->telegram_username,
                'notifications_enabled' => $master->notifications_enabled,
            ],
        ]);
    }

    /**
     * Получить список заказов для мастера
     */
    public function orders(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $status = $request->get('status'); // new, active, completed

        $query = Order::with(['client', 'branch', 'master'])
            ->where('is_deleted', false)
            ->where('master_id', $master->id);

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
     * Получить счетчики заказов для мастера
     */
    public function ordersCount(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        // Новые заказы (new, consultation, diagnostic)
        $newCount = Order::where('is_deleted', false)
            ->where('master_id', $master->id)
            ->whereIn('status', [
                Order::STATUS_NEW,
                Order::STATUS_CONSULTATION,
                Order::STATUS_DIAGNOSTIC,
            ])
            ->count();

        // Заказы в работе (in_work, waiting_parts)
        $inWorkCount = Order::where('is_deleted', false)
            ->where('master_id', $master->id)
            ->whereIn('status', [
                Order::STATUS_IN_WORK,
                Order::STATUS_WAITING_PARTS,
            ])
            ->count();

        return response()->json([
            'new' => $newCount,
            'in_work' => $inWorkCount,
        ]);
    }

    /**
     * Получить товары склада
     */
    public function warehouseItems(Request $request)
    {
        /** @var Master $master */
        $master = $request->user();

        if (!$master) {
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
