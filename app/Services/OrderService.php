<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Client;
use App\Events\Order\OrderStatusChanged;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Создать новый заказ
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $client = Client::firstOrCreate(
                ['phone' => $data['client_phone']],
                [
                    'full_name' => $data['client_name'],
                    'phone' => $data['client_phone'],
                ]
            );

            $orderData = [
                'client_id' => $client->id,
                'order_number' => $this->generateOrderNumber(),
                'service_type' => $data['service_type'],
                'status' => 'new',
                'total_amount' => $this->calculatePrice($data),
            ];

            if ($data['service_type'] === 'sharpening') {
                $orderData['tool_type'] = $data['tool_type'];
                $orderData['total_tools_count'] = $data['total_tools_count'];
            } else {
                $orderData['tool_type'] = $data['tool_type'] ?? 'manicure';
                $orderData['equipment_name'] = $data['equipment_name'];
                $orderData['problem_description'] = $data['problem_description'];
                $orderData['total_tools_count'] = 1;
            }

            // Поля доставки
            $orderData['needs_delivery'] = $data['needs_delivery'] ?? false;
            if ($data['needs_delivery'] ?? false) {
                $orderData['delivery_address'] = $data['delivery_address'];
            }

            $order = Order::create($orderData);

            return $order;
        }, 1);
    }

    /**
     * Обновить статус заказа
     */
    public function updateStatus(Order $order, string $statusSlug): bool
    {
        // Проверяем что статус валидный
        $validStatuses = ['new', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($statusSlug, $validStatuses)) {
            return false;
        }

        $oldStatus = $order->status;
        $order->update(['status' => $statusSlug]);

        // Вызываем событие смены статуса
        event(new OrderStatusChanged($order, $oldStatus, $statusSlug));

        return true;
    }

    /**
     * Получить заказы клиента
     */
    public function getClientOrders(Client $client, int $perPage = 10)
    {
        return $client->orders()
            ->with(['orderStatus', 'serviceType', 'reviews', 'notifications'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получить заказ по номеру
     */
    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)
            ->with(['client', 'orderStatus', 'serviceType', 'reviews', 'notifications', 'orderTools', 'repairs'])
            ->first();
    }

    /**
     * Генерировать номер заказа
     */
    private function generateOrderNumber(): string
    {
        return 'Z' . date('Ymd') . '-' . Str::random(6);
    }

    /**
     * Рассчитывает стоимость заказа
     */
    private function calculatePrice(array $data): float
    {
        $basePrice = 0;

        if ($data['service_type'] === 'sharpening') {
            switch ($data['tool_type']) {
                case 'manicure':
                    $basePrice = 500;
                    break;
                case 'hair':
                    $basePrice = 800;
                    break;
                case 'grooming':
                    $basePrice = 700;
                    break;
                default:
                    $basePrice = 600;
            }

            // Умножаем на количество инструментов
            $basePrice *= $data['total_tools_count'];
        } else {
            // Для ремонта базовая цена
            $basePrice = 1000;
        }

        return $basePrice;
    }
}
