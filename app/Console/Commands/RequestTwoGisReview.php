<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Contracts\TelegramServiceContract;
use App\Contracts\SMSServiceContract;
use Illuminate\Console\Command;
use Carbon\Carbon;

class RequestTwoGisReview extends Command
{
    protected $signature = 'orders:request-2gis-review';
    protected $description = 'Запрос отзывов в 2ГИС';

    public function handle(TelegramServiceContract $telegramService, SMSServiceContract $smsService)
    {
        // Заказы, которые были доставлены 7 дней назад и еще не запрашивался отзыв в 2ГИС
        $orders = Order::where('status', 'delivered')
            ->where('created_at', '<=', Carbon::now()->subDays(7))
            ->whereNull('review_request_sent')
            ->with('client')
            ->get();

        $this->info("Найдено {$orders->count()} заказов для запроса отзыва в 2ГИС");

        foreach ($orders as $order) {
            $message = "⭐ Здравствуйте, {$order->client->full_name}!\n\n";
            $message .= "Спасибо, что выбрали наши услуги по заказу {$order->order_number}!\n\n";
            $message .= "Если Вы остались довольны качеством наших услуг, пожалуйста, оставьте отзыв в 2ГИС.\n";
            $message .= "Это поможет другим людям найти нас и получить качественные услуги.\n\n";
            $message .= "🔗 Ссылка на наш профиль в 2ГИС: [ссылка на 2ГИС]\n\n";
            $message .= "⚠️ Если у Вас есть замечания по качеству наших услуг, пожалуйста, свяжитесь с нами по телефону.\n";
            $message .= "Мы обязательно исправим все недочеты!\n\n";
            $message .= "Спасибо за доверие!\n";
            $message .= "Команда Заточка";

            // Отправляем в Telegram
            if ($order->client->telegram) {
                $telegramService->sendMessage($order->client->telegram, $message);
                $this->info("Запрос отзыва в 2ГИС отправлен в Telegram для заказа {$order->order_number}");
            }

            // Отправляем SMS
            if ($order->client->phone) {
                $smsMessage = "Оставьте отзыв в 2ГИС о заказе {$order->order_number}. Если недовольны - звоните. Заточка";
                $smsService->sendMessage($order->client->phone, $smsMessage);
                $this->info("Запрос отзыва в 2ГИС отправлен SMS для заказа {$order->order_number}");
            }

            // Обновляем заказ
            $order->update(['review_request_sent' => now()]);

            // Сохраняем уведомление
            $order->client->notifications()->create([
                'type' => '2gis_review_request',
                'message_text' => $message,
                'sent_at' => now(),
            ]);
        }

        $this->info('Запросы отзывов в 2ГИС отправлены!');
    }
}
