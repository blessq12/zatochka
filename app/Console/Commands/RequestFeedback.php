<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Contracts\TelegramServiceContract;
use App\Contracts\SMSServiceContract;
use Illuminate\Console\Command;
use Carbon\Carbon;

class RequestFeedback extends Command
{
    protected $signature = 'orders:request-feedback';
    protected $description = 'Запрос обратной связи по завершенным заказам';

    public function handle(TelegramServiceContract $telegramService, SMSServiceContract $smsService)
    {
        // Заказы, которые были доставлены 3 дня назад и еще не запрашивалась обратная связь
        $orders = Order::where('status', 'delivered')
            ->where('created_at', '<=', Carbon::now()->subDays(3))
            ->whereNull('feedback_requested_at')
            ->with('client')
            ->get();

        $this->info("Найдено {$orders->count()} заказов для запроса обратной связи");

        foreach ($orders as $order) {
            $message = "📝 Здравствуйте, {$order->client->full_name}!\n\n";
            $message .= "Надеемся, Вы остались довольны качеством наших услуг по заказу {$order->order_number}.\n\n";
            $message .= "Пожалуйста, поделитесь Вашим мнением о нашей работе.\n";
            $message .= "Ваш отзыв поможет нам стать еще лучше!\n\n";
            $message .= "Если у Вас есть замечания или предложения, мы будем рады их услышать.\n";
            $message .= "Свяжитесь с нами по телефону или в Telegram.\n\n";
            $message .= "Спасибо за доверие!\n";
            $message .= "Команда Заточка";

            // Отправляем в Telegram
            if ($order->client->telegram) {
                $telegramService->sendMessage($order->client->telegram, $message);
                $this->info("Запрос обратной связи отправлен в Telegram для заказа {$order->order_number}");
            }

            // Отправляем SMS
            if ($order->client->phone) {
                $smsMessage = "Как Вам наши услуги по заказу {$order->order_number}? Поделитесь мнением. Заточка";
                $smsService->sendMessage($order->client->phone, $smsMessage);
                $this->info("Запрос обратной связи отправлен SMS для заказа {$order->order_number}");
            }

            // Обновляем заказ
            $order->update(['feedback_requested_at' => now()]);

            // Сохраняем уведомление
            $order->client->notifications()->create([
                'type' => 'feedback_request',
                'message_text' => $message,
                'sent_at' => now(),
            ]);
        }

        $this->info('Запросы обратной связи отправлены!');
    }
}
