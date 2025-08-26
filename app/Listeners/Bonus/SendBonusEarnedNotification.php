<?php

namespace App\Listeners\Bonus;

use App\Events\Bonus\BonusEarned;
use App\Services\TelegramService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendBonusEarnedNotification implements ShouldQueue
{
    protected TelegramService $telegramService;
    protected NotificationService $notificationService;

    public function __construct(TelegramService $telegramService, NotificationService $notificationService)
    {
        $this->telegramService = $telegramService;
        $this->notificationService = $notificationService;
    }

    public function handle(BonusEarned $event): void
    {
        $client = $event->client;
        $amount = $event->amount;
        $reason = $event->reason;
        $order = $event->order;

        Log::info('Bonus earned notification', [
            'client_id' => $client->id,
            'amount' => $amount,
            'reason' => $reason,
            'order_id' => $order?->id,
        ]);

        // Создаем уведомление в системе
        $message = $this->buildMessage($client, $amount, $reason, $order);

        $client->notifications()->create([
            'type' => 'bonus_earned',
            'message_text' => $message,
            'sent_at' => now(),
        ]);

        // Отправляем в Telegram если клиент подключен
        if ($client->telegram && $client->isTelegramVerified()) {
            $this->telegramService->sendBonusEarnedNotification(
                $this->telegramService->getClientChatId($client->id),
                [
                    'client_name' => $client->full_name,
                    'amount' => $amount,
                    'reason' => $reason,
                    'order_number' => $order?->order_number,
                ]
            );
        }
    }

    protected function buildMessage($client, $amount, $reason, $order): string
    {
        $message = "🎁 Здравствуйте, {$client->full_name}!\n\n";

        switch ($reason) {
            case 'order':
                $message .= "Спасибо за заказ {$order->order_number}!\n";
                $message .= "Вам начислено " . number_format($amount, 0) . " бонусных рублей.\n\n";
                $message .= "Бонусы действуют 3 месяца и их можно использовать при следующем заказе.\n";
                break;

            case 'birthday':
                $message .= "В честь Вашего дня рождения начисляем Вам " . number_format($amount, 0) . " бонусных рублей!\n";
                $message .= "Используйте их при следующем заказе.\n";
                break;

            case 'review':
                $message .= "Спасибо за Ваш отзыв! Вам начислено " . number_format($amount, 0) . " бонусных рублей.\n";
                $message .= "Ваше мнение очень важно для нас!\n";
                break;

            default:
                $message .= "Вам начислено " . number_format($amount, 0) . " бонусных рублей.\n";
                $message .= "Причина: {$reason}\n";
        }

        $message .= "\nС уважением, команда Заточка";

        return $message;
    }
}
