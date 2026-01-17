<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        // Проверяем, авторизован ли клиент
        $authenticatedClient = auth('sanctum')->user();

        // Валидация входящих данных (для неавторизованных клиентов поля обязательны)
        $rules = [
            'service_type' => 'required|string|in:sharpening,repair',
            'urgency' => 'nullable|string|in:normal,urgent',
            'problem_description' => 'nullable|string|max:5000',
            'tool_type' => 'nullable|string|max:255',
            'total_tools_count' => 'nullable|integer|min:1',
            'equipment_type' => 'nullable|string|max:255',
            'equipment_name' => 'nullable|string|max:255',
            'needs_delivery' => 'nullable|boolean',
            'delivery_address' => 'nullable|string|max:1000',
            'email' => 'nullable|email|max:255',
        ];

        // Если клиент не авторизован, имя и телефон обязательны
        if (!$authenticatedClient) {
            $rules['client_name'] = 'required|string|min:2|max:255';
            $rules['client_phone'] = 'required|string|min:18|max:18';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Если клиент авторизован - используем его данные
        if ($authenticatedClient) {
            $client = $authenticatedClient;
        } else {
            // Если клиент не авторизован - находим или создаем по телефону
            $phone = preg_replace('/[^0-9+]/', '', $request->client_phone);
            if (!str_starts_with($phone, '+')) {
                $phone = '+7' . preg_replace('/^7/', '', $phone);
            }

            $client = \App\Models\Client::where('phone', $phone)->first();

            if (!$client) {
                $client = \App\Models\Client::create([
                    'full_name' => $request->client_name,
                    'phone' => $phone,
                    'email' => $request->email ?? null,
                ]);
            }
        }

        // Получаем первый филиал (или первый доступный)
        $branch = \App\Models\Branch::first();
        if (!$branch) {
            return response()->json([
                'message' => 'No branch available',
            ], 500);
        }

        // Определяем тип заказа
        $orderType = $request->service_type === 'sharpening'
            ? Order::TYPE_SHARPENING
            : Order::TYPE_REPAIR;

        // Определяем срочность
        $urgency = $request->urgency === 'urgent'
            ? Order::URGENCY_URGENT
            : Order::URGENCY_NORMAL;

        // Подготавливаем данные для создания заказа
        $orderData = [
            'service_type' => $orderType,
            'status' => Order::STATUS_NEW,
            'urgency' => $urgency,
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'problem_description' => $request->problem_description ?? null,
            'tool_type' => $request->tool_type ?? null,
            'total_tools_count' => $request->total_tools_count ?? null,
            'equipment_type' => $request->equipment_type ?? null,
            'equipment_name' => $request->equipment_name ?? null,
            'needs_delivery' => $request->boolean('needs_delivery', false),
            'delivery_address' => $request->delivery_address ?? null,
        ];

        // Создаем заказ
        $order = $client->orders()->create($orderData);

        // Отправляем уведомление в Telegram, если у клиента подтвержден Telegram
        if ($client->telegram_verified_at && $client->telegram) {
            $this->sendOrderNotification($client, $order);
        }

        return response()->json([
            'order' => $order,
            'message' => 'Order created successfully',
        ], 200);
    }

    /**
     * Отправляет уведомление о новом заказе в Telegram
     */
    private function sendOrderNotification($client, $order)
    {
        try {
            // TODO: Реализовать отправку уведомлений через Telegram Bot API
            // Пока просто логируем
            Log::info('Order notification for client', [
                'client_id' => $client->id,
                'order_id' => $order->id,
                'telegram' => $client->telegram,
            ]);
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            Log::error('Telegram notification failed: ' . $e->getMessage());
        }
    }
}
