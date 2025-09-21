<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $client = \App\Models\Client::where('phone', $request->client_phone)->first();

        if (! $client) {
            $client = \App\Models\Client::create([
                'full_name' => $request->client_name,
                'phone' => $request->client_phone,
            ]);
        }

        $order = $client->orders()->create([
            'type' => $request->service_type ?? Order::TYPE_REPAIR,
            'status' => Order::STATUS_NEW,
            'urgency' => Order::URGENCY_NORMAL,
            'client_id' => $client->id,
            'branch_id' => \App\Models\Branch::first()->id,
            ...$request->all(),
        ]);

        // Отправляем уведомление в Telegram, если у клиента подтвержден Telegram
        if ($client->telegram_verified_at && $client->telegramChats()->active()->exists()) {
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
            $telegramChat = $client->telegramChats()->active()->first();

            if (!$telegramChat) {
                return;
            }

            $message = "🎉 *Новый заказ создан!*\n\n";
            $message .= "📋 *Номер заказа:* {$order->order_number}\n";
            $message .= "👤 *Клиент:* {$client->full_name}\n";
            $message .= "📞 *Телефон:* {$client->phone}\n";
            $message .= "🔧 *Тип услуги:* " . Order::getAvailableTypes()[$order->type] . "\n";
            $message .= "📊 *Статус:* " . Order::getAvailableStatuses()[$order->status] . "\n";

            if ($order->estimated_price) {
                $message .= "💰 *Предварительная цена:* " . number_format($order->estimated_price, 2, ',', ' ') . " ₽\n";
            }

            if ($order->problem_description) {
                $message .= "📝 *Описание проблемы:* {$order->problem_description}\n";
            }

            $message .= "\n⏰ *Дата создания:* " . $order->created_at->format('d.m.Y H:i');

            // Отправляем сообщение
            Telegram::sendMessage([
                'chat_id' => $telegramChat->chat_id,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);

            // Сохраняем сообщение в базу данных
            $telegramChat->messages()->create([
                'client_id' => $client->id,
                'content' => $message,
                'direction' => 'outgoing',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            Log::error('Telegram notification failed: ' . $e->getMessage());
        }
    }
}
